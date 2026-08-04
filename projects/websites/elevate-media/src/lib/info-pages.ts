import type { InfoContent } from "@/components/layout/InfoPage";

const pages: Record<string, InfoContent> = {
  "/about": {
    slug: "/about",
    section: "About Us",
    title: "About Elevate Media University",
    subtitle: "Elevate Media University is a dynamic institution committed to producing well-rounded, industry-ready graduates who lead with creativity, integrity and impact.",
    images: ["https://images.unsplash.com/photo-1562774053-701939374585?w=1600&q=80&auto=format&fit=crop"],
    captions: ["A vibrant learning community"],
    description: "Learn about Elevate Media University, a modern university empowering the next generation of professionals.",
    sections: [
      {
        heading: "Who We Are",
        paragraphs: [
          "Elevate Media University is a centre of academic excellence built on innovation, technology and a deep commitment to quality education. We blend rigorous scholarship with hands-on, industry-driven learning so that our graduates do not just find jobs — they create opportunities.",
          "Our community of lecturers, researchers and students work together in a vibrant, inclusive environment where ideas are challenged, talent is nurtured, and leadership is formed.",
        ],
        bullets: [
          "Modern campuses with state-of-the-art learning facilities",
          "Industry-linked programmes reviewed with employer input",
          "A student-centred culture of mentorship and support",
          "Strong focus on creativity, communication and technology",
        ],
      },
      {
        heading: "Our Values",
        cards: [
          { title: "Excellence", text: "We pursue the highest academic standards in everything we do." },
          { title: "Innovation", text: "We embrace technology and new ideas to stay ahead of the world." },
          { title: "Integrity", text: "We act honestly, ethically and transparently at all times." },
          { title: "Inclusion", text: "We celebrate diversity and give every person a fair chance to thrive." },
          { title: "Impact", text: "We measure success by the positive change our community creates." },
        ],
      },
    ],
    cta: { label: "Start Your Journey", href: "/student-apply", note: "Applications are open. Join a university that invests in your future." },
  },
  "/about/vision-mission": {
    slug: "/about/vision-mission",
    section: "About Us",
    title: "Vision & Mission",
    subtitle: "Our vision and mission guide every decision we make as a university.",
    images: [
      "https://images.unsplash.com/photo-1556761175-5973dc0f32e7?w=1600&q=80&auto=format&fit=crop",
      "https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?w=1600&q=80&auto=format&fit=crop",
      "https://images.unsplash.com/photo-1523580846011-d3a5bc25702b?w=1600&q=80&auto=format&fit=crop",
    ],
    captions: ["Shaping business leaders", "Advancing healthcare", "Empowering the next generation of professionals"],
    description: "The vision and mission of Elevate Media University.",
    sections: [
      {
        heading: "Our Vision",
        paragraphs: [
          "To be a leading global university renowned for producing creative, technology-driven and ethical leaders who transform societies through knowledge, innovation and service.",
        ],
      },
      {
        heading: "Our Mission",
        paragraphs: [
          "To provide inclusive, high-quality education and research that equips learners with the knowledge, skills and character to excel in a rapidly changing world.",
        ],
        bullets: [
          "Deliver rigorous, industry-relevant academic programmes",
          "Foster a culture of research, innovation and entrepreneurship",
          "Provide an enabling environment for every student to reach their full potential",
          "Engage local and global communities through meaningful partnerships",
        ],
      },
      {
        heading: "Core Purpose",
        paragraphs: [
          "We exist to empower the next generation of professionals — individuals who think critically, act creatively and lead with purpose in their communities and careers.",
        ],
      },
    ],
  },
  "/about/leadership": {
    slug: "/about/leadership",
    section: "About Us",
    title: "Leadership & Governance",
    subtitle: "Our university is governed with transparency, accountability and a shared vision of excellence.",
    images: ["https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=1600&q=80&auto=format&fit=crop"],
    captions: ["Strategic leadership for academic excellence"],
    description: "Meet the leadership and governance structures of Elevate Media University.",
    sections: [
      {
        heading: "University Council",
        paragraphs: [
          "The University Council is the supreme governing body responsible for the strategic direction, policy approval and oversight of the university. It is chaired by a distinguished individual with wide experience in education, business and public service.",
        ],
      },
      {
        heading: "Vice-Chancellor & Management",
        paragraphs: [
          "The Vice-Chancellor is the academic and administrative head of the university, supported by Deputy Vice-Chancellors and the Registrar. Together they steer teaching, research, finance and institutional development.",
        ],
        cards: [
          { title: "Deputy Vice-Chancellor (Academic)", text: "Oversees curriculum, examinations, admissions and academic standards." },
          { title: "Deputy Vice-Chancellor (Finance & Administration)", text: "Manages resources, infrastructure, staffing and institutional services." },
          { title: "Deputy Vice-Chancellor (Research & Outreach)", text: "Champions research, innovation, partnerships and community engagement." },
        ],
      },
      {
        heading: "Committees",
        bullets: [
          "Senate — the supreme academic authority on teaching and examinations",
          "Academic Programmes Committee — quality and approval of programmes",
          "Research & Ethics Committee — safeguards integrity in research",
          "Finance & Development Committee — prudent management of resources",
        ],
      },
    ],
  },
  "/about/campuses": {
    slug: "/about/campuses",
    section: "About Us",
    title: "Our Campuses",
    subtitle: "We are growing a network of campuses and learning centres to serve students where they are.",
    images: [
      "https://images.unsplash.com/photo-1562774053-701939374585?w=1600&q=80&auto=format&fit=crop",
      "https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=1600&q=80&auto=format&fit=crop",
      "https://images.unsplash.com/photo-1521587760476-6c12a4b040da?w=1600&q=80&auto=format&fit=crop",
    ],
    captions: ["Main campus", "Learning centres", "Modern library"],
    description: "Explore the campuses and learning centres of Elevate Media University.",
    sections: [
      {
        heading: "Main Campus",
        paragraphs: [
          "Our main campus is a vibrant, secure learning environment with lecture theatres, modern laboratories, a well-stocked library, sports facilities and comfortable student accommodation.",
        ],
      },
      {
        heading: "Learning Centres",
        paragraphs: [
          "Through our satellite centres and digital campus, students across the country and beyond can access quality programmes without relocating.",
        ],
        bullets: [
          "Main Campus — full facilities and student life",
          "City Campus — evening and weekend classes for working professionals",
          "Digital Campus — online and distance learning programmes",
          "Affiliated centres in partner regions",
        ],
      },
    ],
  },
  "/about/accreditation": {
    slug: "/about/accreditation",
    section: "About Us",
    title: "Accreditation",
    subtitle: "Our programmes meet the rigorous standards required by national and international regulators.",
    images: [
      "https://images.unsplash.com/photo-1450101499163-c8848c66ca85?w=1600&q=80&auto=format&fit=crop",
      "https://images.unsplash.com/photo-1513475382585-d06e58bcb0e0?w=1600&q=80&auto=format&fit=crop",
    ],
    captions: ["Recognised quality standards", "Approved programmes"],
    description: "Accreditation and regulatory compliance of Elevate Media University.",
    sections: [
      {
        heading: "Institutional Accreditation",
        paragraphs: [
          "Elevate Media University is duly registered and accredited by the national Commission for University Education, and is a member of regional and international university associations.",
        ],
        bullets: [
          "Commission for University Education (CUE) accredited",
          "Inter-University Council for East Africa (IUCEA) member",
          "Association of African Universities (AAU) member",
          "International Association of Universities (IAU) member",
        ],
      },
      {
        heading: "Programme Accreditation",
        paragraphs: [
          "All academic programmes are developed, reviewed and approved through our internal quality assurance processes and submitted for professional body accreditation where applicable.",
        ],
      },
    ],
  },
  "/admission/undergraduate": {
    slug: "/admission/undergraduate",
    section: "Admission",
    title: "Undergraduate Programmes",
    subtitle: "Kickstart your career with a bachelor's degree designed around what employers truly need.",
    description: "Explore undergraduate programmes and how to join Elevate Media University.",
    images: ["https://images.unsplash.com/photo-1758270705087-76e81a5117bd?w=1600&q=80&auto=format&fit=crop"],
    captions: ["Learn from inspiring lecturers"],
    sections: [
      {
        heading: "Why Study With Us",
        bullets: [
          "Industry-linked curricula reviewed with employers and alumni",
          "Hands-on projects, internships and workplace placements",
          "Experienced faculty committed to your success",
          "Access to modern labs, studios and digital tools",
        ],
      },
      {
        heading: "Programme Areas",
        cards: [
          { title: "Media & Communication", text: "Journalism, film, advertising, digital media and public relations." },
          { title: "Business & Management", text: "Accounting, entrepreneurship, marketing, HR and economics." },
          { title: "Computing & Technology", text: "Software development, data science, cybersecurity and AI." },
          { title: "Design & Creative Arts", text: "Graphic design, animation, fashion and performing arts." },
          { title: "Education", text: "Preparing passionate, qualified teachers for the next generation." },
          { title: "Law & Social Sciences", text: "Law, governance, psychology and community development." },
        ],
      },
    ],
    cta: { label: "Apply for Undergraduate Admission", href: "/student-apply", note: "Our undergraduate applications are open — begin your journey today." },
  },
  "/admission/postgraduate": {
    slug: "/admission/postgraduate",
    section: "Admission",
    title: "Postgraduate Programmes",
    subtitle: "Advance your expertise through our master's and doctoral programmes.",
    description: "Explore postgraduate (master's and PhD) programmes at Elevate Media University.",
    images: ["https://images.unsplash.com/photo-1532187863486-abf9dbad1b69?w=1600&q=80&auto=format&fit=crop"],
    captions: ["Advance your expertise through real research"],
    sections: [
      {
        heading: "Masters Programmes",
        bullets: [
          "Master of Business Administration (MBA)",
          "Master of Arts in Communication & Media Studies",
          "Master of Science in Data Science",
          "Master of Education in Educational Leadership",
        ],
      },
      {
        heading: "Doctoral Programmes",
        paragraphs: [
          "Our PhD programmes combine rigorous coursework with supervised original research under the guidance of experienced supervisors. Candidates contribute new knowledge to their fields.",
        ],
      },
      {
        heading: "Eligibility",
        paragraphs: [
          "Admission to postgraduate programmes requires a relevant bachelor's degree (or master's for PhD) from a recognised university, and satisfaction of specific programme entry requirements.",
        ],
      },
    ],
    cta: { label: "Enquire About Postgraduate Admission", href: "/student-apply", note: "Talk to our admissions team about your postgraduate journey." },
  },
  "/admission/online": {
    slug: "/admission/online",
    section: "Admission",
    title: "Online & Distance Learning",
    subtitle: "Study anywhere, anytime with our flexible online and distance programmes.",
    description: "Online and distance learning programmes at Elevate Media University.",
    images: [
      "https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=1600&q=80&auto=format&fit=crop",
      "https://images.unsplash.com/photo-1531545514256-b1400bc00f31?w=1600&q=80&auto=format&fit=crop",
    ],
    captions: ["Study anywhere, anytime", "Live, interactive sessions"],
    sections: [
      {
        heading: "Flexible Learning That Fits Your Life",
        paragraphs: [
          "Our digital campus allows working professionals and remote learners to pursue accredited programmes without leaving their jobs or families. Study through our intuitive online platform with recorded and live sessions.",
        ],
        bullets: [
          "Learn at your own pace with 24/7 access to course materials",
          "Interactive live classes and recorded sessions",
          "Dedicated e-learning support and online examinations",
          "Same certification as on-campus programmes",
        ],
      },
      {
        heading: "How It Works",
        bullets: [
          "Apply online through the admissions portal",
          "Receive your login details and orientation guide",
          "Access lectures, assignments and resources on the e-learning platform",
          "Attend examinations at approved centres or online",
        ],
      },
    ],
    cta: { label: "Apply for Online Learning", href: "/student-apply", note: "Join the digital campus today." },
  },
  "/admission/requirements": {
    slug: "/admission/requirements",
    section: "Admission",
    title: "Admission Requirements",
    subtitle: "Everything you need to know about joining Elevate Media University.",
    description: "Admission requirements for Elevate Media University.",
    images: ["https://images.unsplash.com/photo-1450101499163-c8848c66ca85?w=1600&q=80&auto=format&fit=crop"],
    captions: ["Simple, clear application requirements"],
    sections: [
      {
        heading: "Undergraduate Requirements",
        bullets: [
          "KCSE (or equivalent) minimum C+ for degree programmes",
          "KCSE minimum C (or equivalent) for diploma programmes",
          "Certified copies of academic certificates and transcripts",
          "National ID or passport copy and recent passport-size photos",
        ],
      },
      {
        heading: "Postgraduate Requirements",
        bullets: [
          "Bachelor's degree with a minimum upper second-class honours (or equivalent)",
          "Relevant master's degree for doctoral programmes",
          "Research proposal (for PhD applicants)",
          "Recommendation letters and CV",
        ],
      },
      {
        heading: "Steps to Apply",
        bullets: [
          "Complete the online application form",
          "Upload all required documents",
          "Pay the non-refundable application fee",
          "Track your application status on the portal",
          "Receive your admission letter and join for orientation",
        ],
      },
    ],
    cta: { label: "Check Your Eligibility", href: "/student-apply", note: "Not sure if you qualify? Our team can guide you." },
  },
  "/admission/fees": {
    slug: "/admission/fees",
    section: "Admission",
    title: "Fees Structure",
    subtitle: "Transparent, affordable fee structures and flexible payment options.",
    description: "Fees structure and payment options at Elevate Media University.",
    images: ["https://images.unsplash.com/photo-1554224155-6726b3ff858f?w=1600&q=80&auto=format&fit=crop"],
    captions: ["Transparent, affordable fees"],
    sections: [
      {
        heading: "Affordable & Flexible",
        paragraphs: [
          "We are committed to making quality education accessible. Our fees are competitive and can be paid in instalments per semester. Government student funding and institutional bursaries are available for eligible students.",
        ],
        bullets: [
          "Competitive tuition with transparent breakdowns",
          "Instalment payment plans per semester",
          "Government funding and HELB support for eligible students",
          "Merit and need-based scholarships",
        ],
      },
      {
        heading: "What Fees Cover",
        cards: [
          { title: "Tuition", text: "Cost of academic instruction per programme." },
          { title: "Examination Fees", text: "Continuous assessment and final examinations." },
          { title: "Facility Levy", text: "Use of labs, libraries, studios and technology." },
          { title: "Activity Levy", text: "Student activities, sports and clubs." },
        ],
      },
    ],
    cta: { label: "Get a Fee Breakdown", href: "/student-apply", note: "Contact admissions for a personalised fee structure for your programme." },
  },
  "/admission/calendar": {
    slug: "/admission/calendar",
    section: "Admission",
    title: "Academic Calendar",
    subtitle: "Key dates and milestones for the academic year.",
    description: "Academic calendar and key dates at Elevate Media University.",
    images: ["https://images.unsplash.com/photo-1484480974693-6ca0a78fb36b?w=1600&q=80&auto=format&fit=crop"],
    captions: ["Plan your academic year"],
    sections: [
      {
        heading: "Semester Structure",
        paragraphs: [
          "The academic year is organised into two main semesters with a shorter inter-session. Admission intakes are offered at the start of each semester.",
        ],
        bullets: [
          "January Intake — applications close early December",
          "May Intake — applications close early April",
          "September Intake — applications close early August",
          "Orientation week precedes each semester",
        ],
      },
      {
        heading: "Important Dates",
        bullets: [
          "Semester registration — first two weeks of semester",
          "Mid-semester examinations — week 7",
          "End-of-semester examinations — weeks 15-16",
          "Results release — within 4 weeks after examinations",
          "Graduation ceremonies — twice per year",
        ],
      },
    ],
  },
  "/admission/international": {
    slug: "/admission/international",
    section: "Admission",
    title: "International Students",
    subtitle: "Welcome to our global family of students from across the world.",
    description: "International students admission at Elevate Media University.",
    images: ["https://images.unsplash.com/photo-1436491865332-7a61a109cc05?w=1600&q=80&auto=format&fit=crop"],
    captions: ["Join our global family"],
    sections: [
      {
        heading: "Studying With Us From Abroad",
        paragraphs: [
          "Students from across Africa and beyond choose Elevate Media University for its quality programmes, welcoming community and vibrant campus life. Our international office supports you from application to graduation.",
        ],
        bullets: [
          "Dedicated international admissions support",
          "Assistance with student pass applications",
          "Affordable living costs in a safe, friendly environment",
          "Online programmes available before you travel",
        ],
      },
      {
        heading: "Application Checklist",
        bullets: [
          "Completed online application form",
          "Recognised qualifications equivalent to local requirements",
          "Valid passport and academic transcripts",
          "Proof of English proficiency (where applicable)",
        ],
      },
    ],
    cta: { label: "Apply as an International Student", href: "/student-apply", note: "Our international office is ready to assist you." },
  },
  "/students/affairs": {
    slug: "/students/affairs",
    section: "Students",
    title: "Student Affairs",
    subtitle: "Supporting students beyond the classroom so they can focus on succeeding.",
    description: "Student affairs and support services at Elevate Media University.",
    sections: [
      {
        heading: "Office of Student Affairs",
        paragraphs: [
          "The Dean of Students and student affairs team coordinate the non-academic side of university life — from discipline and conduct to welfare, housing and student activities.",
        ],
        bullets: [
          "Guidance and counselling services",
          "Student conduct and disciplinary procedures",
          "Student ID cards and verification letters",
          "Coordination of student governance and representation",
        ],
      },
      {
        heading: "How We Help",
        cards: [
          { title: "Counselling", text: "Confidential support for personal and academic challenges." },
          { title: "Orientation", text: "A smooth start for all new students." },
          { title: "Advocacy", text: "We champion the interests of every student." },
        ],
      },
    ],
  },
  "/students/welfare": {
    slug: "/students/welfare",
    section: "Students",
    title: "Student Welfare",
    subtitle: "Your health, safety and wellbeing are our priority.",
    description: "Student welfare and wellbeing services at Elevate Media University.",
    sections: [
      {
        heading: "Welfare Services",
        bullets: [
          "On-campus health services and wellness programmes",
          "Accommodation options with full-time wardens",
          "Safe, secure campus environment with 24/7 security",
          "Financial aid and emergency assistance",
        ],
      },
      {
        heading: "Wellbeing & Support",
        paragraphs: [
          "We provide free and confidential counselling, mental health awareness programmes and peer support networks so that no student faces their challenges alone.",
        ],
      },
    ],
  },
  "/students/clubs": {
    slug: "/students/clubs",
    section: "Students",
    title: "Clubs & Societies",
    subtitle: "Find your people, discover your passions and grow beyond the lecture hall.",
    description: "Student clubs and societies at Elevate Media University.",
    sections: [
      {
        heading: "Something For Everyone",
        paragraphs: [
          "University life is about more than classes. Our clubs and societies give you a platform to develop leadership, creativity and lifelong friendships.",
        ],
        bullets: [
          "Student Government & Leadership Council",
          "Media, Journalism & Creative Writing Clubs",
          "Tech, Robotics & Innovation Societies",
          "Sports, Music, Drama and Cultural Societies",
          "Entrepreneurship & Mentorship Programmes",
          "Debate, Mentorship and Community Outreach Clubs",
        ],
      },
      {
        heading: "Starting A Club",
        paragraphs: [
          "Students can register new clubs through the Office of Student Affairs. A simple constitution and a committed team of members is all it takes to start something great.",
        ],
      },
    ],
  },
  "/students/accommodation": {
    slug: "/students/accommodation",
    section: "Students",
    title: "Accommodation",
    subtitle: "Comfortable, safe and affordable housing close to your learning facilities.",
    description: "Student accommodation at Elevate Media University.",
    sections: [
      {
        heading: "On-Campus Hostels",
        paragraphs: [
          "Our on-campus hostels offer a secure, convenient environment with Wi-Fi, water, power and round-the-clock security. Students live minutes from lectures, the library and sports grounds.",
        ],
        bullets: [
          "Single and shared rooms available",
          "Furnished rooms with study space",
          "Wardens and security available 24/7",
          "Cafeterias and common rooms on site",
        ],
      },
      {
        heading: "Off-Campus Options",
        paragraphs: [
          "For students who prefer private housing, our accommodation office maintains a vetted list of safe, reasonably-priced rentals near campus.",
        ],
      },
    ],
  },
  "/students/scholarships": {
    slug: "/students/scholarships",
    section: "Students",
    title: "Scholarships & Bursaries",
    subtitle: "Financial support to ensure talent is never held back by cost.",
    description: "Scholarships and bursaries at Elevate Media University.",
    sections: [
      {
        heading: "Types of Support",
        bullets: [
          "Merit-based scholarships for top performing students",
          "Need-based bursaries for students facing financial hardship",
          "Government student funding for eligible applicants",
          "Alumni and corporate sponsored scholarships",
          "Sports and talent scholarships",
        ],
      },
      {
        heading: "How To Apply",
        paragraphs: [
          "Scholarship and bursary applications open at the start of each academic year. Students apply through the Office of Student Affairs, attach supporting documents, and awards are made by a transparent committee.",
        ],
      },
    ],
  },
  "/students/graduation": {
    slug: "/students/graduation",
    section: "Students",
    title: "Graduation",
    subtitle: "Celebrating the milestone your hard work has earned.",
    description: "Graduation information at Elevate Media University.",
    sections: [
      {
        heading: "Graduation Ceremonies",
        paragraphs: [
          "We celebrate our graduates twice a year with ceremonies that honour their achievements in front of family, friends and distinguished guests. Graduates receive transcripts and certificates in addition to their gowns and hoods.",
        ],
        bullets: [
          "Two graduation ceremonies each year",
          "Graduation gown and hood hire arrangements",
          "Certificates, transcripts and alumni membership on award",
          "Livestream available for friends and family",
        ],
      },
      {
        heading: "Before You Graduate",
        bullets: [
          "Clear all outstanding fees and library obligations",
          "Confirm your details in the graduation portal",
          "Collect your academic attire",
          "Attend the graduation rehearsal",
        ],
      },
    ],
  },
  "/students/alumni": {
    slug: "/students/alumni",
    section: "Students",
    title: "Alumni Association",
    subtitle: "Once an Elevate graduate, always part of the Elevate family.",
    description: "The alumni association of Elevate Media University.",
    sections: [
      {
        heading: "Stay Connected",
        paragraphs: [
          "Our alumni are leaders in media, business, technology, education and public service around the world. The Alumni Association keeps you connected to the university, your classmates and new opportunities.",
        ],
        bullets: [
          "Career services and job referrals for alumni",
          "Networking events and mentorship programmes",
          "Alumni newsletter and success stories",
          "Opportunities to give back and mentor current students",
        ],
      },
      {
        heading: "Join The Association",
        paragraphs: [
          "All graduates automatically become members of the Alumni Association upon graduation. There are no fees to register — just update your details and stay involved.",
        ],
      },
    ],
  },
  "/academics/programmes": {
    slug: "/academics/programmes",
    section: "Academics",
    title: "Academic Programmes",
    subtitle: "A broad range of accredited programmes from certificate to doctoral level.",
    description: "Academic programmes offered at Elevate Media University.",
    images: ["https://images.unsplash.com/photo-1541339907198-e08756dedf3f?w=1600&q=80&auto=format&fit=crop"],
    captions: ["A community of learners on campus"],
    sections: [
      {
        heading: "Our Programme Portfolio",
        paragraphs: [
          "We offer programmes across six schools, from certificate and diploma courses through bachelor's, master's and doctoral degrees, all designed around the demands of the modern workplace.",
        ],
        bullets: [
          "Certificates & Diplomas — foundational and technical skills",
          "Undergraduate Degrees — comprehensive professional education",
          "Postgraduate Diplomas & Masters — advanced specialisation",
          "Doctoral Programmes — original research and scholarship",
        ],
      },
      {
        heading: "Explore By School",
        cards: [
          { title: "School of Media & Communication", text: "Journalism, film, digital media, advertising and PR." },
          { title: "School of Business & Economics", text: "Management, accounting, marketing, economics and finance." },
          { title: "School of Computing & Technology", text: "Software, data science, cybersecurity and emerging tech." },
          { title: "School of Design & Creative Arts", text: "Visual, digital and performing arts programmes." },
          { title: "School of Education", text: "Teacher training and educational leadership." },
          { title: "School of Law & Social Sciences", text: "Law, governance, psychology and development studies." },
        ],
      },
    ],
    cta: { label: "Browse All Courses", href: "/courses", note: "See the full list of courses across all schools." },
  },
  "/academics/schools": {
    slug: "/academics/schools",
    section: "Academics",
    title: "Schools & Faculties",
    subtitle: "Six schools delivering specialist knowledge and practical skills.",
    description: "Schools and faculties at Elevate Media University.",
    images: [
      "https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?w=1600&q=80&auto=format&fit=crop",
      "https://images.unsplash.com/photo-1589829545856-d10d557cf95f?w=1600&q=80&auto=format&fit=crop",
      "https://images.unsplash.com/photo-1532094349884-543bc11b234d?w=1600&q=80&auto=format&fit=crop",
      "https://images.unsplash.com/photo-1559027615-cd4628902d4a?w=1600&q=80&auto=format&fit=crop",
    ],
    captions: [
      "School of Computing & Technology",
      "School of Law & Social Sciences",
      "Science and research laboratories",
      "School of Design & Creative Arts",
    ],
    sections: [
      {
        heading: "Our Schools",
        cards: [
          { title: "Media & Communication", text: "Storytelling and media production for the digital age." },
          { title: "Business & Economics", text: "Building the next generation of business leaders." },
          { title: "Computing & Technology", text: "Innovation at the intersection of software and society." },
          { title: "Design & Creative Arts", text: "Where imagination meets professional craft." },
          { title: "Education", text: "Preparing outstanding educators for the classroom." },
          { title: "Law & Social Sciences", text: "Justice, governance and human development." },
        ],
      },
      {
        heading: "Quality Teaching",
        paragraphs: [
          "Each school is led by a Dean and staffed by qualified academics with both research credentials and real-world industry experience.",
        ],
      },
    ],
  },
  "/academics/e-learning": {
    slug: "/academics/e-learning",
    section: "Academics",
    title: "E-Learning",
    subtitle: "A digital learning platform that puts your studies in your pocket.",
    description: "E-learning platform and digital campus at Elevate Media University.",
    images: ["https://images.unsplash.com/photo-1546410531-bb4caa6b424d?w=1600&q=80&auto=format&fit=crop"],
    captions: ["Learn anywhere, on any device"],
    sections: [
      {
        heading: "Your Digital Campus",
        paragraphs: [
          "Our e-learning platform hosts course materials, recorded lectures, quizzes, assignments and discussion forums. Access it from any device, anywhere, at any time.",
        ],
        bullets: [
          "24/7 access to course content and recordings",
          "Online assignment submission and grading",
          "Discussion forums and virtual classes",
          "Digital library and research databases",
          "Mobile-friendly experience",
        ],
      },
      {
        heading: "Getting Started",
        bullets: [
          "Use your student ID to log in to the e-learning portal",
          "Complete the online orientation module",
          "Join your course pages and meet your lecturers",
        ],
      },
    ],
  },
  "/academics/examinations": {
    slug: "/academics/examinations",
    section: "Academics",
    title: "Examinations",
    subtitle: "Fair, transparent and well-organised examinations.",
    description: "Examinations office at Elevate Media University.",
    images: ["https://images.unsplash.com/photo-1521737711867-e3b97375f902?w=1600&q=80&auto=format&fit=crop"],
    captions: ["The examinations team at work"],
    sections: [
      {
        heading: "Examinations Office",
        paragraphs: [
          "The Examinations Office manages the scheduling, administration and marking of all university examinations, ensuring academic integrity and fairness at every stage.",
        ],
        bullets: [
          "Published examination timetables each semester",
          "Secure and confidential handling of results",
          "Transparent re-mark and appeal procedures",
          "Support for students with special examination needs",
        ],
      },
      {
        heading: "For Students",
        bullets: [
          "Check your timetable and venue early",
          "Arrive with your student ID",
          "Results are released on the portal within four weeks of the last paper",
        ],
      },
    ],
  },
  "/academics/calendar": {
    slug: "/academics/calendar",
    section: "Academics",
    title: "Academic Calendar",
    subtitle: "The roadmap for each semester at Elevate Media University.",
    description: "Academic calendar for Elevate Media University.",
    images: ["https://cdn.pixabay.com/photo/2018/09/09/17/28/timetable-3665089_1280.jpg"],
    captions: ["The academic year at a glance"],
    sections: [
      {
        heading: "Semester Timeline",
        bullets: [
          "Registration — first two weeks of the semester",
          "Teaching — weeks 1 to 14",
          "Mid-semester examinations — week 7",
          "Revision — week 14",
          "End-of-semester examinations — weeks 15 and 16",
          "Results release — within four weeks",
        ],
      },
      {
        heading: "Semester Dates",
        paragraphs: [
          "The academic year runs across three intakes — January, May and September — giving students flexibility in when they begin and pace their studies.",
        ],
      },
    ],
  },
  "/academics/quality-assurance": {
    slug: "/academics/quality-assurance",
    section: "Academics",
    title: "Quality Assurance",
    subtitle: "Safeguarding the standards behind every Elevate qualification.",
    description: "Quality assurance at Elevate Media University.",
    images: ["https://images.unsplash.com/photo-1581094794329-c8112a89af12?w=1600&q=80&auto=format&fit=crop"],
    captions: ["Verifying our standards"],
    sections: [
      {
        heading: "Our Commitment To Quality",
        paragraphs: [
          "The Directorate of Quality Assurance develops, monitors and continuously improves the standards of our programmes, teaching and student support.",
        ],
        bullets: [
          "Programme development and periodic review",
          "Student and staff feedback surveys",
          "External examiner moderation",
          "Institutional self-assessment and accreditation audits",
          "Staff development and teaching excellence programmes",
        ],
      },
    ],
  },
  "/academics/staff": {
    slug: "/academics/staff",
    section: "Academics",
    title: "Academic Staff",
    subtitle: "Learn from lecturers who both research and practice their fields.",
    description: "Academic staff at Elevate Media University.",
    images: ["https://images.unsplash.com/photo-1560250097-0b93528c311a?w=1600&q=80&auto=format&fit=crop"],
    captions: ["Qualified academic staff"],
    sections: [
      {
        heading: "World-Class Faculty",
        paragraphs: [
          "Our academic staff combine strong academic credentials with practical industry experience. Many of our lecturers continue to work as consultants, creators and researchers in their fields, bringing real-world insight into every classroom.",
        ],
        bullets: [
          "Qualified academics with master's and doctoral degrees",
          "Industry practitioners in media, tech, business and law",
          "Active researchers and published authors",
          "Committed mentors available for student guidance",
        ],
      },
      {
        heading: "Join Our Faculty",
        paragraphs: [
          "We are always looking for passionate educators and researchers. Qualified professionals interested in teaching are encouraged to apply.",
        ],
      },
    ],
    cta: { label: "Join Our Teaching Staff", href: "/teacher-apply", note: "Become part of a university that values great teaching." },
  },
  "/academics/teaching": {
    slug: "/academics/teaching",
    section: "Academics",
    title: "Teaching & Learning",
    subtitle: "Modern, interactive teaching methods designed for real learning.",
    description: "Teaching and learning approach at Elevate Media University.",
    images: ["https://images.unsplash.com/photo-1524178232363-1fb2b075b655?w=1600&q=80&auto=format&fit=crop"],
    captions: ["University teaching that engages every student"],
    sections: [
      {
        heading: "Our Approach",
        paragraphs: [
          "We move beyond one-way lectures. Our teaching blends theory with practice through case studies, group projects, studio work, simulations and workplace placements.",
        ],
        bullets: [
          "Small classes that encourage participation",
          "Project-based and collaborative learning",
          "Technology-enabled classrooms and studios",
          "Internships and industry placements",
          "Mentorship from lecturers and alumni",
        ],
      },
      {
        heading: "Learning Support",
        cards: [
          { title: "Academic Advising", text: "Every student has an advisor for guidance." },
          { title: "Writing & Numeracy Support", text: "Free support to strengthen core skills." },
          { title: "Peer Tutoring", text: "Learn with the help of successful senior students." },
        ],
      },
    ],
  },
  "/research": {
    slug: "/research",
    section: "Research",
    title: "Research & Innovation",
    subtitle: "Turning curiosity into discoveries that benefit society.",
    description: "Research and innovation at Elevate Media University.",
    sections: [
      {
        heading: "A Research-Focused University",
        paragraphs: [
          "Elevate Media University is committed to meaningful research that addresses real challenges in media, technology, business, education and society. Our researchers publish in reputable journals and collaborate with institutions worldwide.",
        ],
        bullets: [
          "Thematic research in media, tech and social innovation",
          "Seed grants for early-career researchers",
          "Graduate research assistantships",
          "Annual research conference and journal",
        ],
      },
      {
        heading: "Impact Areas",
        cards: [
          { title: "Digital Media & Society", text: "Understanding media's role in modern communities." },
          { title: "Applied Technology", text: "Practical solutions using data and AI." },
          { title: "Creative Industries", text: "Growing the creative economy." },
          { title: "Education Innovation", text: "Improving how people learn." },
        ],
      },
    ],
    cta: { label: "Collaborate With Our Researchers", href: "/research/collaborations", note: "Partner with us on research, innovation and outreach." },
  },
  "/research/centres": {
    slug: "/research/centres",
    section: "Research",
    title: "Research Centres",
    subtitle: "Focused centres driving deep expertise in key fields.",
    description: "Research centres at Elevate Media University.",
    sections: [
      {
        heading: "Our Centres",
        cards: [
          { title: "Centre for Digital Media Research", text: "Studying media, journalism and communication in the digital age." },
          { title: "Centre for Applied Data Science", text: "Solving real problems with data, analytics and AI." },
          { title: "Centre for Creative Economy", text: "Supporting innovation in arts, design and entertainment." },
          { title: "Centre for Teaching & Learning Research", text: "Evidence-based improvement of education." },
        ],
      },
      {
        heading: "What Centres Do",
        paragraphs: [
          "Centres bring together multidisciplinary teams of researchers, host seminars and conferences, secure grant funding and publish findings that inform policy and practice.",
        ],
      },
    ],
  },
  "/research/postgraduate": {
    slug: "/research/postgraduate",
    section: "Research",
    title: "Postgraduate Research",
    subtitle: "Undertake original research under expert supervision.",
    description: "Postgraduate research (PhD and masters) at Elevate Media University.",
    sections: [
      {
        heading: "Research Degrees",
        paragraphs: [
          "Our master's and doctoral programmes are pathways for students who want to make original contributions to knowledge. You will work closely with a dedicated supervisory team from proposal to thesis examination.",
        ],
        bullets: [
          "Masters with research component",
          "Doctoral (PhD) programmes in all schools",
          "Structured research training and methodology courses",
          "Ethics review and research integrity support",
        ],
      },
      {
        heading: "Support For Researchers",
        bullets: [
          "Experienced supervisors matched to your topic",
          "Research grants and conference travel support",
          "Access to journals, datasets and labs",
          "Seminar series to share and sharpen your work",
        ],
      },
    ],
  },
  "/research/publications": {
    slug: "/research/publications",
    section: "Research",
    title: "Publications",
    subtitle: "The published work of our scholars and students.",
    description: "Research publications from Elevate Media University.",
    sections: [
      {
        heading: "Scholarly Output",
        paragraphs: [
          "Our academic community publishes in peer-reviewed journals, books and conference proceedings. We also produce our own university journal, research bulletins and working papers.",
        ],
        bullets: [
          "Elevate Media University Research Journal",
          "Peer-reviewed articles and conference papers",
          "Books and book chapters by our faculty",
          "Student research published in our journal",
          "Working papers and policy briefs",
        ],
      },
      {
        heading: "Find Our Work",
        paragraphs: [
          "Publications are available through our digital repository, university library and open-access platforms.",
        ],
      },
    ],
  },
  "/research/innovation": {
    slug: "/research/innovation",
    section: "Research",
    title: "Innovation & Incubation",
    subtitle: "Turning ideas and research into startups and real products.",
    description: "Innovation and incubation hub at Elevate Media University.",
    sections: [
      {
        heading: "The Innovation Hub",
        paragraphs: [
          "Our innovation and incubation hub supports students and researchers to transform ideas into viable products, services and businesses. We provide mentorship, workspace, prototyping tools and access to investors.",
        ],
        bullets: [
          "Incubation space for student startups",
          "Mentorship from entrepreneurs and industry leaders",
          "Prototyping labs and media production studios",
          "Pitch events and demo days",
          "Intellectual property and patent guidance",
        ],
      },
      {
        heading: "Success Stories",
        paragraphs: [
          "Elevate innovators have launched media production houses, ed-tech platforms, design studios and social enterprises — proof that our ideas can change the world.",
        ],
      },
    ],
  },
  "/research/ethics": {
    slug: "/research/ethics",
    section: "Research",
    title: "Research Ethics",
    subtitle: "Integrity and responsibility at the heart of our research.",
    description: "Research ethics at Elevate Media University.",
    sections: [
      {
        heading: "Ethics Committee",
        paragraphs: [
          "All research involving human participants must be reviewed and approved by our Research Ethics Committee before it begins. This safeguards the rights, dignity and safety of participants.",
        ],
        bullets: [
          "Mandatory ethical review for all research",
          "Informed consent and confidentiality standards",
          "Data protection compliance",
          "Responsible conduct of research training",
        ],
      },
      {
        heading: "Research Integrity",
        paragraphs: [
          "We uphold the highest standards of honesty, transparency and accountability in research, including fair authorship, proper citation and honest reporting of results.",
        ],
      },
    ],
  },
  "/research/collaborations": {
    slug: "/research/collaborations",
    section: "Research",
    title: "Collaborations & Partnerships",
    subtitle: "Partnering locally and globally to amplify our impact.",
    images: [
      "https://images.unsplash.com/photo-1521791136064-7986c2920216?w=1600&q=80&auto=format&fit=crop",
      "https://images.unsplash.com/photo-1552664730-d307ca884978?w=1600&q=80&auto=format&fit=crop",
    ],
    captions: ["University and industry partnerships", "Collaborations that amplify impact"],
    description: "Collaborations and partnerships at Elevate Media University.",
    sections: [
      {
        heading: "Our Partners",
        paragraphs: [
          "We collaborate with universities, industry, government and civil society to advance research, enrich teaching and serve communities. These partnerships create internships, joint research and exchange opportunities for our students.",
        ],
        bullets: [
          "University exchange and joint-degree partnerships",
          "Industry partnerships for placements and research",
          "Government and development agency collaborations",
          "Media and technology company partnerships",
        ],
      },
      {
        heading: "Partner With Us",
        paragraphs: [
          "Organisations interested in collaboration can contact our research office to explore research partnerships, sponsorship, student internships or staff development programmes.",
        ],
      },
    ],
  },
  "/library": {
    slug: "/library",
    section: "Library & Media Desk",
    title: "Library Services",
    subtitle: "A modern library built for study, research and discovery.",
    description: "Library services at Elevate Media University.",
    sections: [
      {
        heading: "Your Study Home",
        paragraphs: [
          "Our library is a bright, welcoming space with thousands of books, journals and digital resources, plus quiet study areas, group rooms and computer workstations.",
        ],
        bullets: [
          "Physical collection of books, journals and media",
          "Digital databases and e-books",
          "Quiet and group study spaces",
          "Lending and inter-library loan services",
          "Library training and research support",
        ],
      },
      {
        heading: "Opening & Access",
        paragraphs: [
          "The library is open six days a week during term, with extended hours during examination periods. Students access resources using their university ID card.",
        ],
      },
    ],
  },
  "/library/e-resources": {
    slug: "/library/e-resources",
    section: "Library & Media Desk",
    title: "E-Resources",
    subtitle: "Digital journals, e-books and databases at your fingertips.",
    description: "E-resources and digital library at Elevate Media University.",
    sections: [
      {
        heading: "Digital Collections",
        paragraphs: [
          "Our e-resources give you 24/7 access to thousands of scholarly journals, e-books and research databases — on campus or from anywhere in the world.",
        ],
        bullets: [
          "Peer-reviewed journal databases",
          "E-books across all disciplines",
          "Research databases and citation tools",
          "Remote access with your student credentials",
        ],
      },
      {
        heading: "Get Help",
        paragraphs: [
          "Not sure where to start? Our librarians offer training sessions and one-on-one support for research and referencing.",
        ],
      },
    ],
  },
  "/library/repository": {
    slug: "/library/repository",
    section: "Library & Media Desk",
    title: "Digital Repository",
    subtitle: "A permanent home for the university's intellectual output.",
    description: "Digital repository at Elevate Media University.",
    sections: [
      {
        heading: "Preserve & Share",
        paragraphs: [
          "Our digital repository collects, preserves and provides open access to theses, research papers, publications and other scholarly work produced by the university community.",
        ],
        bullets: [
          "Student theses and dissertations",
          "Faculty research publications",
          "Conference proceedings",
          "Institutional reports and policy documents",
          "Open access for the global community",
        ],
      },
    ],
  },
  "/library/media-desk": {
    slug: "/library/media-desk",
    section: "Library & Media Desk",
    title: "Media Desk",
    subtitle: "The creative hub for media production, equipment and expertise.",
    description: "Media desk and media services at Elevate Media University.",
    sections: [
      {
        heading: "Media Services",
        paragraphs: [
          "The Media Desk supports teaching and storytelling across the university — offering cameras, studios, editing suites and professional guidance for student projects and institutional media.",
        ],
        bullets: [
          "Camera, lighting and audio equipment loans",
          "TV and podcast studios",
          "Editing suites and post-production support",
          "Filming support for university events",
          "Training on professional media equipment",
        ],
      },
      {
        heading: "Who Can Use It",
        paragraphs: [
          "The Media Desk is open to students and staff working on academic projects, campus events and creative productions. Bookings are made online or at the desk.",
        ],
      },
    ],
  },
  "/library/news": {
    slug: "/library/news",
    section: "Library & Media Desk",
    title: "News & Publications",
    subtitle: "The latest stories, announcements and publications from Elevate.",
    description: "News and publications from Elevate Media University.",
    sections: [
      {
        heading: "Stay Informed",
        paragraphs: [
          "Follow the latest news from the university — campus announcements, events, research highlights, student achievements and official publications.",
        ],
        bullets: [
          "Campus news and announcements",
          "Event listings and invitations",
          "Research and innovation highlights",
          "Official publications and newsletters",
          "Media enquiries and press contact",
        ],
      },
      {
        heading: "Contact The Media Desk",
        paragraphs: [
          "For media enquiries, partnership coverage or press interviews, contact the Media Desk — we are happy to help share Elevate's story.",
        ],
      },
    ],
  },
};

export function getInfoPage(slug: string): InfoContent {
  return pages[slug];
}
