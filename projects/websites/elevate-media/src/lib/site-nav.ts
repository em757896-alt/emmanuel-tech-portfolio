export type NavLink = { label: string; href: string };
export type NavColumn = { heading: string; links: NavLink[] };
export type NavItem = {
  label: string;
  href?: string;
  columns?: NavColumn[];
  wide?: boolean;
};

export const navItems: NavItem[] = [
  { label: "Home", href: "/" },
  {
    label: "About Us",
    columns: [
      {
        heading: "The University",
        links: [
          { label: "About Elevate Media University", href: "/about" },
          { label: "Vision & Mission", href: "/about/vision-mission" },
          { label: "Leadership & Governance", href: "/about/leadership" },
          { label: "Our Campuses", href: "/about/campuses" },
        ],
      },
      {
        heading: "Recognition",
        links: [
          { label: "Achievements", href: "/achievements" },
          { label: "Accreditation", href: "/about/accreditation" },
          { label: "Collaborations & Partnerships", href: "/research/collaborations" },
        ],
      },
    ],
  },
  {
    label: "Admission",
    columns: [
      {
        heading: "Apply Now",
        links: [
          { label: "Student Application", href: "/student-apply" },
          { label: "Teacher Application", href: "/teacher-apply" },
        ],
      },
      {
        heading: "Programmes",
        links: [
          { label: "Undergraduate Programmes", href: "/admission/undergraduate" },
          { label: "Postgraduate Programmes", href: "/admission/postgraduate" },
          { label: "Online & Distance Learning", href: "/admission/online" },
        ],
      },
      {
        heading: "Admission Information",
        links: [
          { label: "Admission Requirements", href: "/admission/requirements" },
          { label: "Fees Structure", href: "/admission/fees" },
          { label: "Academic Calendar", href: "/admission/calendar" },
          { label: "International Students", href: "/admission/international" },
        ],
      },
    ],
  },
  {
    label: "Students",
    wide: true,
    columns: [
      {
        heading: "Student Portal",
        links: [
          { label: "Student Login", href: "/student-login" },
          { label: "Dashboard", href: "/dashboard" },
          { label: "My Courses", href: "/dashboard/courses" },
          { label: "Assignments", href: "/dashboard/assignments" },
          { label: "Examinations", href: "/dashboard/exams" },
          { label: "Results", href: "/dashboard/results" },
          { label: "Attendance", href: "/dashboard/attendance" },
        ],
      },
      {
        heading: "Student Life",
        links: [
          { label: "Announcements", href: "/dashboard/announcements" },
          { label: "Student Affairs", href: "/students/affairs" },
          { label: "Student Welfare", href: "/students/welfare" },
          { label: "Clubs & Societies", href: "/students/clubs" },
          { label: "Accommodation", href: "/students/accommodation" },
        ],
      },
      {
        heading: "Support & Growth",
        links: [
          { label: "Scholarships & Bursaries", href: "/students/scholarships" },
          { label: "Graduation", href: "/students/graduation" },
          { label: "Alumni Association", href: "/students/alumni" },
        ],
      },
    ],
  },
  {
    label: "Academics",
    wide: true,
    columns: [
      {
        heading: "Programmes",
        links: [
          { label: "Academic Programmes", href: "/academics/programmes" },
          { label: "Schools & Faculties", href: "/academics/schools" },
          { label: "All Courses", href: "/courses" },
          { label: "E-Learning", href: "/academics/e-learning" },
        ],
      },
      {
        heading: "Examinations & Quality",
        links: [
          { label: "Examinations", href: "/academics/examinations" },
          { label: "Academic Calendar", href: "/academics/calendar" },
          { label: "Quality Assurance", href: "/academics/quality-assurance" },
        ],
      },
      {
        heading: "Academic Community",
        links: [
          { label: "Academic Staff", href: "/academics/staff" },
          { label: "Teaching & Learning", href: "/academics/teaching" },
        ],
      },
    ],
  },
  {
    label: "Research",
    columns: [
      {
        heading: "Research",
        links: [
          { label: "Research & Innovation", href: "/research" },
          { label: "Research Centres", href: "/research/centres" },
          { label: "Postgraduate Research", href: "/research/postgraduate" },
        ],
      },
      {
        heading: "Output & Partnerships",
        links: [
          { label: "Publications", href: "/research/publications" },
          { label: "Innovation & Incubation", href: "/research/innovation" },
          { label: "Research Ethics", href: "/research/ethics" },
          { label: "Collaborations & Partnerships", href: "/research/collaborations" },
        ],
      },
    ],
  },
  {
    label: "Library & Media Desk",
    columns: [
      {
        heading: "Library",
        links: [
          { label: "Library Services", href: "/library" },
          { label: "E-Resources", href: "/library/e-resources" },
          { label: "Digital Repository", href: "/library/repository" },
        ],
      },
      {
        heading: "Media Desk",
        links: [
          { label: "Media Desk", href: "/library/media-desk" },
          { label: "News & Publications", href: "/library/news" },
        ],
      },
    ],
  },
];
