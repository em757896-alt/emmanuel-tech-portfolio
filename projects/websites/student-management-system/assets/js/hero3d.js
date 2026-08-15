/* Elevate Media College — hero3d.js
   Particle galaxy behind the home hero (classic script, uses global THREE from three.min.js).
   Degrades silently to CSS background if WebGL / THREE is unavailable. */
(function () {
    'use strict';

    var canvas = document.getElementById('hero3d');
    if (!canvas || !window.THREE) return;

    var reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    var renderer;
    try {
        renderer = new THREE.WebGLRenderer({ canvas: canvas, alpha: true, antialias: true });
    } catch (e) {
        canvas.remove();
        return;
    }

    var scene = new THREE.Scene();
    var camera = new THREE.PerspectiveCamera(70, 1, 0.1, 100);
    camera.position.z = 10;

    /* Colored galaxy spiral built from three layers of particles */
    var palettes = [
        [[0x6d5df6, 0.9], [0x00d4ff, 0.7], [0xffffff, 0.5]],   // violet + cyan core
        [[0x00d4ff, 0.7], [0x2dd4a7, 0.5], [0xffffff, 0.4]],    // cyan shell
        [[0x6d5df6, 0.5], [0xffffff, 0.3], [0x00d4ff, 0.3]]     // faint outer haze
    ];

    var parts = [];
    for (var L = 0; L < 3; L++) {
        var count = 1300;
        var geo = new THREE.BufferGeometry();
        var pos = new Float32Array(count * 3);
        var col = new Float32Array(count * 3);
        var pal = palettes[L];
        var radius = 3.2 + L * 2.1;

        for (var i = 0; i < count; i++) {
            var r = radius * (0.4 + Math.random() * 0.6);
            var a = Math.random() * Math.PI * 2;
            var spread = 0.35 * (0.35 + (r / radius) * 0.9);
            var jitter = (Math.random() - 0.5) * spread;
            var z = (Math.random() - 0.5) * 2.6 * (L * 0.7 + 0.6);

            pos[i * 3] = Math.cos(a + jitter) * r;
            pos[i * 3 + 1] = Math.sin(a + jitter) * r * 0.92 + jitter * 0.6;
            pos[i * 3 + 2] = z;

            var c = pal[Math.floor(Math.random() * pal.length)];
            var cc = new THREE.Color(c[0]);
            var bright = c[1] * (0.5 + Math.random() * 0.6);
            col[i * 3] = cc.r * bright;
            col[i * 3 + 1] = cc.g * bright;
            col[i * 3 + 2] = cc.b * bright;
        }

        geo.setAttribute('position', new THREE.BufferAttribute(pos, 3));
        geo.setAttribute('color', new THREE.BufferAttribute(col, 3));

        var mat = new THREE.PointsMaterial({
            size: 0.055 + L * 0.02,
            vertexColors: true,
            transparent: true,
            opacity: 0.95 - L * 0.24,
            blending: THREE.AdditiveBlending,
            depthWrite: false
        });

        var points = new THREE.Points(geo, mat);
        points.rotation.x = -0.45;
        points.rotation.z = L * 0.5;
        scene.add(points);
        parts.push(points);
    }

    /* A few larger accent orbs to feel like buildings rising */
    for (var b = 0; b < 10; b++) {
        var r2 = 2.2 + Math.random() * 4.6;
        var a2 = Math.random() * Math.PI * 2;
        var orb = new THREE.Mesh(
            new THREE.BoxGeometry(0.06 + Math.random() * 0.12, 0.1 + Math.random() * 0.5, 0.06 + Math.random() * 0.12),
            new THREE.MeshBasicMaterial({
                color: Math.random() > 0.5 ? 0x00d4ff : 0x6d5df6,
                transparent: true,
                opacity: 0.8
            })
        );
        orb.position.set(Math.cos(a2) * r2, Math.sin(a2) * r2, (Math.random() - 0.5) * 3);
        orb.rotation.z = -0.45;
        scene.add(orb);
    }

    function resize() {
        var w = canvas.clientWidth, h = canvas.clientHeight;
        if (!w || !h) return;
        renderer.setSize(w, h, false);
        camera.aspect = w / h;
        camera.updateProjectionMatrix();
    }
    window.addEventListener('resize', resize);
    resize();

    var mouse = { x: 0, y: 0 };
    document.addEventListener('pointermove', function (e) {
        mouse.x = (e.clientX / window.innerWidth - 0.5) * 2;
        mouse.y = (e.clientY / window.innerHeight - 0.5) * 2;
    });

    var clock = new THREE.Clock();
    function loop() {
        requestAnimationFrame(loop);
        var t = clock.getElapsedTime();
        for (var i = 0; i < parts.length; i++) {
            parts[i].rotation.z += (reduced ? 0.0003 : 0.0016) * (i + 1) * 0.6;
        }
        camera.position.x = THREE.MathUtils.lerp(camera.position.x, mouse.x * 0.9, reduced ? 0 : 0.03);
        camera.position.y = THREE.MathUtils.lerp(camera.position.y, -mouse.y * 0.6, reduced ? 0 : 0.03);
        camera.lookAt(scene.position);
        renderer.render(scene, camera);
    }
    loop();
})();
