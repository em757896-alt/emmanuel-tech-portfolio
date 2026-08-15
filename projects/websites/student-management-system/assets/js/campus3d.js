/* Elevate Media College — campus3d.js
   Interactive 3D campus: rotate, zoom, click buildings/classrooms/avatars.
   Loaded as an ES module (full CDN URLs, no import map required). */
import * as THREE from 'https://cdn.jsdelivr.net/npm/three@0.160.0/build/three.module.js';
import { OrbitControls } from 'https://cdn.jsdelivr.net/npm/three@0.160.0/examples/jsm/controls/OrbitControls.js';

(function () {
    'use strict';

    var DATA = window.EMC_CAMPUS || { buildings: [], classrooms: [], timetable: {}, students: [] };
    var stage = document.getElementById('campus3d');
    if (!stage || !DATA.buildings.length) { document.documentElement.classList.add('no-webgl'); return; }

    var loading = document.getElementById('campusLoading');
    var panel = document.getElementById('campusPanel');

    var renderer;
    var scene, camera, controls;
    var clock = new THREE.Clock();
    var raycaster = new THREE.Raycaster();
    var pointer = new THREE.Vector2(-2, -2);
    var clickables = [];
    var hovered = null;
    var studentOrbs = [];
    var classroomMarkers = [];
    var userInteracted = false;

    /* ---------------- Setup ---------------- */
    try {
        var isTouch = navigator.maxTouchPoints > 0;
        renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true, powerPreference: 'high-performance' });
        renderer.setPixelRatio(Math.min(window.devicePixelRatio, isTouch ? 1.5 : 2));
        renderer.setSize(stage.clientWidth, stage.clientHeight);
        stage.appendChild(renderer.domElement);
    } catch (e) {
        document.documentElement.classList.add('no-webgl');
        return;
    }

    scene = new THREE.Scene();
    scene.fog = new THREE.Fog(0x070b1d, 55, 110);

    camera = new THREE.PerspectiveCamera(50, stage.clientWidth / stage.clientHeight, 0.1, 300);
    camera.position.set(22, 15, 24);

    controls = new OrbitControls(camera, renderer.domElement);
    controls.target.set(0, 1.6, 0);
    controls.enableDamping = true;
    controls.dampingFactor = 0.08;
    controls.minDistance = 8;
    controls.maxDistance = 46;
    controls.maxPolarAngle = Math.PI / 2 - 0.02;
    controls.autoRotate = true;
    controls.autoRotateSpeed = 0.55;

    controls.addEventListener('start', function () { userInteracted = true; controls.autoRotate = false; });

    /* Lights */
    scene.add(new THREE.HemisphereLight(0xdbe6ff, 0x0c1030, 0.95));
    var sun = new THREE.DirectionalLight(0xffffff, 1.15);
    sun.position.set(18, 26, 12);
    scene.add(sun);
    var rim = new THREE.DirectionalLight(0x00d4ff, 0.45);
    rim.position.set(-16, 8, -14);
    scene.add(rim);

    /* ---------------- Ground ---------------- */
    function groundTexture() {
        var cv = document.createElement('canvas');
        cv.width = 1024; cv.height = 1024;
        var ctx = cv.getContext('2d');
        var g = ctx.createRadialGradient(512, 512, 60, 512, 512, 512);
        g.addColorStop(0, '#141b3d');
        g.addColorStop(0.55, '#0d1230');
        g.addColorStop(1, '#070b1d');
        ctx.fillStyle = g;
        ctx.fillRect(0, 0, 1024, 1024);
        ctx.strokeStyle = 'rgba(255,255,255,.05)';
        ctx.lineWidth = 2;
        for (var r = 140; r <= 500; r += 70) {
            ctx.beginPath(); ctx.arc(512, 512, r, 0, Math.PI * 2); ctx.stroke();
        }
        for (var i = 0; i < 260; i++) {
            var a = Math.random() * Math.PI * 2;
            var rad = Math.random() * 500;
            ctx.fillStyle = 'rgba(255,255,255,' + (0.03 + Math.random() * 0.05) + ')';
            ctx.beginPath(); ctx.arc(512 + Math.cos(a) * rad, 512 + Math.sin(a) * rad, 1.4, 0, Math.PI * 2); ctx.fill();
        }
        return new THREE.CanvasTexture(cv);
    }

    var ground = new THREE.Mesh(
        new THREE.CircleGeometry(80, 96),
        new THREE.MeshStandardMaterial({ map: groundTexture(), roughness: 1, metalness: 0 })
    );
    ground.rotation.x = -Math.PI / 2;
    scene.add(ground);

    /* Plaza */
    function ring(rIn, rOut, color, opacity) {
        var m = new THREE.Mesh(
            new THREE.RingGeometry(rIn, rOut, 96),
            new THREE.MeshBasicMaterial({ color: color, transparent: true, opacity: opacity, depthWrite: false })
        );
        m.rotation.x = -Math.PI / 2;
        m.position.y = 0.02;
        return m;
    }
    scene.add(ring(0, 6.4, 0x00d4ff, 0.07));
    scene.add(ring(6.4, 6.7, 0x00d4ff, 0.5));
    scene.add(ring(7.1, 7.4, 0x2dd4a7, 0.35));
    scene.add(ring(13.5, 13.8, 0x6d5df6, 0.18));

    /* ---------------- Helpers ---------------- */
    function makeLabel(text, color, scale) {
        var cv = document.createElement('canvas');
        cv.width = 512; cv.height = 128;
        var ctx = cv.getContext('2d');
        ctx.font = '700 58px "Space Grotesk", Inter, sans-serif';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.shadowColor = 'rgba(0,0,0,.92)';
        ctx.shadowBlur = 22;
        ctx.fillStyle = color || '#eaf0ff';
        ctx.fillText(text, 256, 66);
        var tex = new THREE.CanvasTexture(cv);
        tex.anisotropy = 4;
        var sprite = new THREE.Sprite(new THREE.SpriteMaterial({ map: tex, depthTest: false, transparent: true }));
        var s = scale || 4.6;
        sprite.scale.set(s, s / 4, 1);
        return sprite;
    }

    function makeWindowsTexture(color, cols, rows) {
        var cv = document.createElement('canvas');
        cv.width = 512; cv.height = 256;
        var ctx = cv.getContext('2d');
        ctx.fillStyle = '#0c0f2a';
        ctx.fillRect(0, 0, 512, 256);
        var cw = 44, ch = 28, gx = 12, gy = 18;
        var xs = (512 - cols * cw - (cols - 1) * gx) / 2;
        var ys = (256 - rows * ch - (rows - 1) * gy) / 2;
        ctx.fillStyle = 'rgba(215,232,255,.9)';
        for (var r = 0; r < rows; r++) {
            for (var c = 0; c < cols; c++) {
                ctx.fillRect(xs + c * (cw + gx), ys + r * (ch + gy), cw, ch);
            }
        }
        ctx.globalAlpha = 0.22;
        ctx.fillStyle = color;
        ctx.fillRect(0, 0, 512, 256);
        ctx.globalAlpha = 1;
        return new THREE.CanvasTexture(cv);
    }

    function buildBuilding(b) {
        var group = new THREE.Group();
        var w = b.w || 4, h = b.h || 2.2, d = b.d || 2.6;
        var color = new THREE.Color(b.color || '#6d5df6');

        var bodyMat = new THREE.MeshStandardMaterial({ color: color, roughness: 0.62, metalness: 0.18 });
        var windowMat = new THREE.MeshStandardMaterial({
            map: makeWindowsTexture(b.color || '#6d5df6', Math.max(3, Math.round(w * 3.2)), 3),
            emissive: new THREE.Color(b.color || '#6d5df6'),
            emissiveIntensity: 0.32,
            roughness: 0.5,
            metalness: 0.1
        });

        var body = new THREE.Mesh(new THREE.BoxGeometry(w, h, d), [bodyMat, bodyMat, bodyMat, bodyMat, windowMat, windowMat]);
        body.position.y = h / 2;
        group.add(body);

        var roof = new THREE.Mesh(
            new THREE.ConeGeometry(Math.max(w, d) * 0.62, 0.6, 4),
            new THREE.MeshStandardMaterial({ color: color.clone().multiplyScalar(0.55), roughness: 0.7, metalness: 0.2 })
        );
        roof.rotation.y = Math.PI / 4;
        roof.position.y = h + 0.3;
        group.add(roof);

        var door = new THREE.Mesh(
            new THREE.BoxGeometry(0.7, 0.85, 0.16),
            new THREE.MeshStandardMaterial({ color: 0x1b1f3a, roughness: 0.9 })
        );
        door.position.set(0, 0.42, d / 2 + 0.08);
        group.add(door);

        var label = makeLabel(b.label, '#ffffff', w * 1.15);
        label.position.y = h + 1.6;
        group.add(label);

        group.position.set(b.x, 0, b.z);
        group.rotation.y = Math.atan2(-b.x, -b.z);

        group.userData = { kind: 'building', data: b, bodyMat: bodyMat, windowMat: windowMat, base: group.scale.clone() };
        return group;
    }

    function buildMarker(c) {
        var group = new THREE.Group();
        var mat = new THREE.MeshStandardMaterial({
            color: new THREE.Color(c.color || '#00d4ff'),
            emissive: new THREE.Color(c.color || '#00d4ff'),
            emissiveIntensity: 0.65,
            roughness: 0.35
        });
        var geo = new THREE.IcosahedronGeometry(0.34, 0);
        var m = new THREE.Mesh(geo, mat);
        group.add(m);

        var ring = new THREE.Mesh(
            new THREE.RingGeometry(0.34, 0.52, 24),
            new THREE.MeshBasicMaterial({ color: c.color || '#00d4ff', transparent: true, opacity: 0.4, depthWrite: false })
        );
        ring.rotation.x = -Math.PI / 2;
        ring.position.y = -0.5;
        group.add(ring);

        var label = makeLabel(c.name, '#ffffff', 2.7);
        label.position.y = 1.15;
        group.add(label);

        group.position.set(c.x, 1.35, c.z);
        group.userData = { kind: 'classroom', data: c, mat: mat, baseY: 1.35 };
        return group;
    }

    function buildOrb(s) {
        var group = new THREE.Group();
        var color = new THREE.Color(s.color || '#6d5df6');
        var mat = new THREE.MeshStandardMaterial({
            color: color,
            emissive: color,
            emissiveIntensity: 0.35,
            roughness: 0.35,
            metalness: 0.15
        });
        var mesh = new THREE.Mesh(new THREE.SphereGeometry(0.52, 24, 24), mat);
        group.add(mesh);

        var ring = new THREE.Mesh(
            new THREE.RingGeometry(0.52, 0.74, 32),
            new THREE.MeshBasicMaterial({ color: color, transparent: true, opacity: 0.35, depthWrite: false })
        );
        ring.rotation.x = -Math.PI / 2;
        ring.position.y = -0.66;
        group.add(ring);

        var label = makeLabel(s.initials || '?', '#ffffff', 1.5);
        label.position.y = 0.95;
        group.add(label);

        var n = DATA.students.length || 1;
        var i = DATA.students.indexOf(s);
        var angle = (i / n) * Math.PI * 2 - Math.PI / 2;
        var radius = 7.4;
        group.position.set(Math.cos(angle) * radius, 1.15, Math.sin(angle) * radius);
        group.userData = { kind: 'student', data: s, mat: mat, baseY: 1.15, angle: angle, radius: radius };
        return group;
    }

    /* Populate */
    DATA.buildings.forEach(function (b) {
        var g = buildBuilding(b);
        scene.add(g);
        clickables.push(g);
    });

    DATA.classrooms.forEach(function (c) {
        var g = buildMarker(c);
        scene.add(g);
        clickables.push(g);
        classroomMarkers.push(g);
    });

    DATA.students.forEach(function (s) {
        var g = buildOrb(s);
        scene.add(g);
        clickables.push(g);
        studentOrbs.push(g);
    });

    /* ---------------- Interaction ---------------- */
    function pick(x, y) {
        pointer.set(x, y);
        raycaster.setFromCamera(pointer, camera);
        var hit = raycaster.intersectObjects(clickables, true);
        return hit.length ? hit[0].object : null;
    }

    function clearHover() {
        if (hovered) {
            var ud = hovered.userData;
            if (ud.kind === 'building') {
                ud.bodyMat.emissive && ud.bodyMat.emissive.setHex(0x000000);
                ud.windowMat.emissiveIntensity = 0.32;
                hovered.scale.copy(ud.base);
            }
            if (ud.kind === 'classroom') { ud.mat.emissiveIntensity = 0.65; }
            if (ud.kind === 'student') { ud.mat.emissiveIntensity = 0.35; }
            hovered = null;
            stage.style.cursor = 'grab';
        }
    }

    function setHover(obj) {
        clearHover();
        hovered = obj;
        var ud = obj.userData;
        stage.style.cursor = 'pointer';
        if (ud.kind === 'building') {
            ud.bodyMat.emissive = new THREE.Color(ud.data.color || '#6d5df6');
            ud.bodyMat.emissiveIntensity = 0.45;
            ud.windowMat.emissiveIntensity = 0.7;
            obj.scale.multiplyScalar(1.035);
        }
        if (ud.kind === 'classroom') { ud.mat.emissiveIntensity = 1.25; }
        if (ud.kind === 'student') { ud.mat.emissiveIntensity = 0.9; }
    }

    renderer.domElement.addEventListener('pointermove', function (e) {
        var rect = renderer.domElement.getBoundingClientRect();
        var x = ((e.clientX - rect.left) / rect.width) * 2 - 1;
        var y = -((e.clientY - rect.top) / rect.height) * 2 + 1;
        var obj = pick(x, y);
        if (obj) setHover(obj); else clearHover();
    });

    renderer.domElement.addEventListener('pointerleave', clearHover);

    var downPos = null;
    renderer.domElement.addEventListener('pointerdown', function (e) {
        downPos = { x: e.clientX, y: e.clientY };
    });

    renderer.domElement.addEventListener('click', function (e) {
        if (downPos && (Math.abs(e.clientX - downPos.x) > 6 || Math.abs(e.clientY - downPos.y) > 6)) return;
        var rect = renderer.domElement.getBoundingClientRect();
        var x = ((e.clientX - rect.left) / rect.width) * 2 - 1;
        var y = -((e.clientY - rect.top) / rect.height) * 2 + 1;
        var obj = pick(x, y);
        if (!obj) { closePanel(); return; }
        var ud = obj.userData;
        if (ud.kind === 'building') openBuilding(ud.data);
        else if (ud.kind === 'classroom') openClassroom(ud.data);
        else if (ud.kind === 'student') openProfile(ud.data);
    });

    /* ---------------- Panel & modals ---------------- */
    function closePanel() {
        if (panel) panel.classList.remove('open');
    }

    function openBuilding(b) {
        if (!panel) return;
        document.getElementById('cpTitle').textContent = b.label;
        document.getElementById('cpTag').textContent = b.sub || '';
        document.getElementById('cpDesc').textContent = b.desc || '';

        var meta = document.getElementById('cpMeta');
        meta.innerHTML = '';
        function row(icon, text) {
            var d = document.createElement('div');
            d.innerHTML = '<i class="fa-solid ' + icon + '"></i>';
            d.appendChild(document.createTextNode(text));
            meta.appendChild(d);
        }
        if (b.dept) {
            row('fa-user-tie', 'Head: ' + b.dept.head);
            row('fa-calendar', 'Established ' + b.dept.established);
        }

        var actions = document.getElementById('cpActions');
        actions.innerHTML = '';
        (b.actions || []).forEach(function (a) {
            var el = document.createElement('a');
            el.className = 'btn btn-primary btn-sm';
            el.href = a.href;
            el.innerHTML = '<i class="fa-solid ' + (a.icon || 'fa-arrow-right') + '"></i> ' + a.label;
            actions.appendChild(el);
        });

        panel.classList.add('open');
    }

    window.openCampusTimetable = function (id) {
        var c = DATA.classrooms.find(function (x) { return x.id === id; });
        var tt = DATA.timetable[id] || [];
        var html = '<div class="modal-head"><h3>' + (c ? c.name : 'Classroom') + ' &middot; Timetable</h3>' +
            '<button class="modal-close" aria-label="Close"><i class="fa-solid fa-xmark"></i></button></div>';
        if (tt.length) {
            html += '<div class="table-scroll"><table class="tt-table"><thead><tr><th>Day</th><th>Time</th><th>Course</th><th>Lecturer</th></tr></thead><tbody>';
            tt.forEach(function (r) {
                html += '<tr><td><span class="badge violet">' + r.day + '</span></td>' +
                    '<td class="tt-time">' + r.start + ' &ndash; ' + r.end + '</td>' +
                    '<td class="tt-course">' + r.course + '</td><td>' + r.lecturer + '</td></tr>';
            });
            html += '</tbody></table></div>';
        } else {
            html += '<p class="text-dim">No sessions are scheduled for this classroom yet.</p>';
        }
        html += '<div class="cp-actions" style="margin-top:16px">' +
            '<a class="btn btn-ghost btn-sm" href="timetable.php?classroom=' + id + '"><i class="fa-solid fa-calendar-days"></i> Full timetable</a>' +
            '<button class="btn btn-primary btn-sm" data-close-modal>Close</button></div>';
        window.openModal(html);
    };

    window.openCampusProfile = function (s) {
        var html = '<div class="modal-head"><h3>Student profile</h3><button class="modal-close" aria-label="Close"><i class="fa-solid fa-xmark"></i></button></div>' +
            '<div class="avatar avatar-lg" style="background:' + s.color + '; margin-bottom:16px">' + s.initials + '</div>' +
            '<h3 style="margin:0 0 2px">' + s.name + '</h3>' +
            '<p class="text-dim" style="margin:0 0 18px">' + s.course + ' &middot; ' + s.dept + '</p>' +
            '<div class="cp-meta">' +
            '<div><i class="fa-solid fa-envelope"></i> ' + s.email + '</div>' +
            '<div><i class="fa-solid fa-phone"></i> ' + s.phone + '</div></div>' +
            '<div class="cp-actions" style="margin-top:16px"><button class="btn btn-primary btn-sm" data-close-modal>Close</button></div>';
        window.openModal(html);
    };

    function openClassroom(c) { window.openCampusTimetable(c.id); }
    function openProfile(s) { window.openCampusProfile(s); }

    var panelClose = panel && panel.querySelector('[data-close-panel]');
    if (panelClose) {
        panelClose.addEventListener('click', closePanel);
    }
    document.addEventListener('click', function (e) {
        if (panel && panel.classList.contains('open') && !panel.contains(e.target) && !e.target.closest('.modal-backdrop')) {
            var rect = renderer.domElement.getBoundingClientRect();
            var inside = e.clientX >= rect.left && e.clientX <= rect.right && e.clientY >= rect.top && e.clientY <= rect.bottom;
            if (inside) closePanel();
        }
    });

    /* ---------------- Animate ---------------- */
    function animate() {
        requestAnimationFrame(animate);
        var t = clock.getElapsedTime();

        studentOrbs.forEach(function (orb, i) {
            var ud = orb.userData;
            orb.position.y = ud.baseY + Math.sin(t * 1.1 + i) * 0.16;
            orb.rotation.y = t * 0.6 + i;
            var g = orb.children[0];
            g.rotation.x = t * 0.8;
        });

        classroomMarkers.forEach(function (m, i) {
            var ud = m.userData;
            var pulse = 0.65 + (Math.sin(t * 2.2 + i * 1.3) + 1) * 0.28;
            ud.mat.emissiveIntensity = (hovered === m ? 1.25 : pulse);
            m.position.y = ud.baseY + Math.sin(t * 1.8 + i) * 0.12;
            m.children[0].rotation.x = t * 1.4;
        });

        controls.update();
        renderer.render(scene, camera);
    }

    function onResize() {
        if (!stage || !stage.clientWidth) return;
        camera.aspect = stage.clientWidth / stage.clientHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(stage.clientWidth, stage.clientHeight);
    }
    window.addEventListener('resize', onResize);

    onResize();
    animate();

    if (loading) {
        requestAnimationFrame(function () { loading.classList.add('hide'); });
    }
})();
