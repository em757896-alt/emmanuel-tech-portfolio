import {
  Laptop, Cpu, Briefcase, Atom, Pencil, Users, GraduationCap, Heart,
  Scale, Palette, TreePine, Leaf, Radio, Globe, BookOpenCheck, Dumbbell,
} from "lucide-react";

export type Course = {
  title: string;
  code: string;
  duration: string;
  credits: number;
  description: string;
  skills: string[];
};

export type Department = {
  id: string;
  name: string;
  degree: string;
  courses: Course[];
};

export type Faculty = {
  id: string;
  name: string;
  icon: any;
  color: string;
  description: string;
  departments: Department[];
};

export const faculties: Faculty[] = [
  {
    id: "computing",
    name: "Faculty of Computing & Informatics",
    icon: Laptop,
    color: "bg-blue-100 text-blue-600",
    description: "Software, systems, AI, and data technologies for the modern world.",
    departments: [
      {
        id: "cs",
        name: "Computer Science",
        degree: "Bachelor of Science in Computer Science",
        courses: [
          { title: "Introduction to Programming", code: "CS101", duration: "1 Semester", credits: 3, description: "Programming logic, variables, control structures, and basic data structures using Python.", skills: ["Python", "Problem-solving", "Algorithm design"] },
          { title: "Data Structures & Algorithms", code: "CS201", duration: "1 Semester", credits: 4, description: "Arrays, linked lists, trees, graphs, sorting, and searching algorithms.", skills: ["Data structures", "Algorithm analysis", "Big-O notation"] },
          { title: "Object-Oriented Programming", code: "CS202", duration: "1 Semester", credits: 3, description: "OOP principles: encapsulation, inheritance, polymorphism, and design patterns.", skills: ["Java", "Design patterns", "SOLID principles"] },
          { title: "Database Systems", code: "CS301", duration: "1 Semester", credits: 4, description: "Relational database design, SQL, normalization, and NoSQL databases.", skills: ["SQL", "Database design", "PostgreSQL"] },
          { title: "Operating Systems", code: "CS302", duration: "1 Semester", credits: 4, description: "Process management, memory, file systems, and concurrency.", skills: ["Linux", "Shell scripting", "System administration"] },
          { title: "Software Engineering", code: "CS303", duration: "1 Semester", credits: 4, description: "SDLC, agile methodologies, version control, and testing.", skills: ["Git", "Agile/Scrum", "CI/CD"] },
          { title: "Computer Networks", code: "CS401", duration: "1 Semester", credits: 3, description: "OSI model, TCP/IP, routing, and network security.", skills: ["Networking", "TCP/IP", "Wireshark"] },
          { title: "Artificial Intelligence", code: "CS402", duration: "1 Semester", credits: 4, description: "Machine learning, neural networks, and NLP.", skills: ["Machine learning", "TensorFlow", "Data preprocessing"] },
          { title: "Web Development", code: "CS403", duration: "1 Semester", credits: 3, description: "Full-stack web development with modern frameworks.", skills: ["React/Next.js", "Node.js", "REST APIs"] },
          { title: "Cybersecurity Fundamentals", code: "CS404", duration: "1 Semester", credits: 3, description: "Cryptography, penetration testing, and ethical hacking.", skills: ["Cryptography", "Pen testing", "Security auditing"] },
        ],
      },
      {
        id: "se",
        name: "Software Engineering",
        degree: "Bachelor of Science in Software Engineering",
        courses: [
          { title: "Software Design & Architecture", code: "SE201", duration: "1 Semester", credits: 4, description: "Software design principles, architectural patterns, and UML modeling.", skills: ["UML", "Design patterns", "Architecture"] },
          { title: "DevOps & Cloud Computing", code: "SE301", duration: "1 Semester", credits: 4, description: "CI/CD pipelines, containerization, and cloud deployment.", skills: ["Docker", "AWS/Azure", "Jenkins"] },
          { title: "Mobile Application Development", code: "SE302", duration: "1 Semester", credits: 3, description: "Cross-platform mobile app development with React Native.", skills: ["React Native", "Flutter", "Mobile UI/UX"] },
          { title: "Quality Assurance & Testing", code: "SE401", duration: "1 Semester", credits: 3, description: "Software testing strategies, automation, and quality metrics.", skills: ["Selenium", "Jest", "Test automation"] },
        ],
      },
      {
        id: "ds",
        name: "Data Science",
        degree: "Bachelor of Science in Data Science",
        courses: [
          { title: "Statistics for Data Science", code: "DS101", duration: "1 Semester", credits: 4, description: "Probability, distributions, hypothesis testing, and regression.", skills: ["R", "Python", "Statistical analysis"] },
          { title: "Machine Learning", code: "DS301", duration: "1 Semester", credits: 4, description: "Supervised and unsupervised learning, model evaluation.", skills: ["Scikit-learn", "Python", "Model tuning"] },
          { title: "Big Data Analytics", code: "DS302", duration: "1 Semester", credits: 4, description: "Processing large datasets with Spark and Hadoop.", skills: ["Spark", "Hadoop", "Data pipelines"] },
          { title: "Data Visualization", code: "DS401", duration: "1 Semester", credits: 3, description: "Interactive dashboards and visual storytelling with data.", skills: ["Tableau", "Power BI", "D3.js"] },
        ],
      },
    ],
  },
  {
    id: "engineering",
    name: "Faculty of Engineering",
    icon: Cpu,
    color: "bg-amber-100 text-amber-600",
    description: "Build the infrastructure, machines, and systems of tomorrow.",
    departments: [
      {
        id: "ee",
        name: "Electrical & Electronic Engineering",
        degree: "Bachelor of Science in Electrical Engineering",
        courses: [
          { title: "Circuit Analysis", code: "EE101", duration: "1 Semester", credits: 4, description: "Electrical circuits, Ohm's law, Kirchhoff's laws.", skills: ["Circuit design", "SPICE", "Network analysis"] },
          { title: "Digital Electronics", code: "EE201", duration: "1 Semester", credits: 4, description: "Logic gates, combinational circuits, microprocessor architecture.", skills: ["Logic design", "FPGA", "VHDL"] },
          { title: "Signals & Systems", code: "EE301", duration: "1 Semester", credits: 4, description: "Fourier analysis, Laplace transforms, filter design.", skills: ["Signal processing", "MATLAB", "Filter design"] },
          { title: "Power Systems", code: "EE401", duration: "1 Semester", credits: 3, description: "Generation, transmission, and distribution of electrical power.", skills: ["Power generation", "Grid systems", "Renewable energy"] },
        ],
      },
      {
        id: "ce",
        name: "Civil Engineering",
        degree: "Bachelor of Science in Civil Engineering",
        courses: [
          { title: "Structural Analysis", code: "CE201", duration: "1 Semester", credits: 4, description: "Forces, moments, and structural behavior of materials.", skills: ["AutoCAD", "Structural modeling", "Load analysis"] },
          { title: "Geotechnical Engineering", code: "CE301", duration: "1 Semester", credits: 4, description: "Soil mechanics, foundation design, and earthworks.", skills: ["Soil testing", "Foundation design", "Lab analysis"] },
          { title: "Construction Management", code: "CE302", duration: "1 Semester", credits: 3, description: "Project planning, cost estimation, and site management.", skills: ["MS Project", "Cost estimation", "Site management"] },
          { title: "Transportation Engineering", code: "CE401", duration: "1 Semester", credits: 3, description: "Highway design, traffic flow, and urban planning.", skills: ["Highway design", "Traffic analysis", "Urban planning"] },
        ],
      },
      {
        id: "me",
        name: "Mechanical Engineering",
        degree: "Bachelor of Science in Mechanical Engineering",
        courses: [
          { title: "Thermodynamics", code: "ME101", duration: "1 Semester", credits: 4, description: "Energy, heat transfer, and engine cycles.", skills: ["Heat transfer", "Engine design", "Energy systems"] },
          { title: "Fluid Mechanics", code: "ME201", duration: "1 Semester", credits: 4, description: "Fluid behavior, flow dynamics, and hydraulic systems.", skills: ["CFD", "Hydraulics", "Pipe design"] },
          { title: "Manufacturing Processes", code: "ME301", duration: "1 Semester", credits: 3, description: "Machining, welding, casting, and additive manufacturing.", skills: ["CNC machining", "3D printing", "Quality control"] },
          { title: "Robotics & Automation", code: "ME401", duration: "1 Semester", credits: 4, description: "Industrial robots, sensors, and automated systems.", skills: ["PLC programming", "Robotics", "Sensors"] },
        ],
      },
      {
        id: "energy",
        name: "Energy Engineering",
        degree: "Bachelor of Science in Energy Engineering",
        courses: [
          { title: "Renewable Energy Systems", code: "EN201", duration: "1 Semester", credits: 4, description: "Solar, wind, hydro, and biomass energy systems.", skills: ["Solar design", "Wind turbines", "Energy auditing"] },
          { title: "Energy Storage & Grid Integration", code: "EN301", duration: "1 Semester", credits: 4, description: "Battery systems, grid integration, and smart grids.", skills: ["Battery tech", "Smart grids", "Energy management"] },
        ],
      },
    ],
  },
  {
    id: "business",
    name: "Faculty of Business & Management",
    icon: Briefcase,
    color: "bg-green-100 text-green-600",
    description: "Leadership, strategy, accounting, and organizational management.",
    departments: [
      {
        id: "ba",
        name: "Business Administration",
        degree: "Bachelor of Business Administration",
        courses: [
          { title: "Principles of Management", code: "BA101", duration: "1 Semester", credits: 3, description: "Management theories, planning, organizing, leading.", skills: ["Strategic planning", "Leadership", "Decision making"] },
          { title: "Financial Accounting", code: "BA201", duration: "1 Semester", credits: 4, description: "Financial statements, analysis, and interpretation.", skills: ["Financial reporting", "Accounting software", "GAAP"] },
          { title: "Marketing Management", code: "BA301", duration: "1 Semester", credits: 3, description: "Consumer behavior, digital marketing, brand management.", skills: ["Market research", "Digital marketing", "Brand strategy"] },
          { title: "Human Resource Management", code: "BA302", duration: "1 Semester", credits: 3, description: "Recruitment, training, compensation, and labor relations.", skills: ["Recruitment", "Payroll", "Employee relations"] },
          { title: "Strategic Management", code: "BA401", duration: "1 Semester", credits: 4, description: "Business strategy, competitive analysis, and implementation.", skills: ["SWOT analysis", "Porter's Five Forces", "Strategy execution"] },
        ],
      },
      {
        id: "af",
        name: "Accounting & Finance",
        degree: "Bachelor of Commerce in Accounting & Finance",
        courses: [
          { title: "Cost Accounting", code: "AF201", duration: "1 Semester", credits: 4, description: "Cost analysis, budgeting, and cost control methods.", skills: ["Cost analysis", "Budgeting", "Variance analysis"] },
          { title: "Corporate Finance", code: "AF301", duration: "1 Semester", credits: 4, description: "Capital structure, investment decisions, and financial planning.", skills: ["Valuation", "Capital budgeting", "Financial modeling"] },
          { title: "Auditing & Assurance", code: "AF401", duration: "1 Semester", credits: 3, description: "Audit procedures, internal controls, and reporting.", skills: ["Audit methodology", "Internal controls", "Compliance"] },
        ],
      },
      {
        id: "eco",
        name: "Economics",
        degree: "Bachelor of Science in Economics",
        courses: [
          { title: "Microeconomics", code: "EC101", duration: "1 Semester", credits: 3, description: "Supply and demand, market structures, consumer theory.", skills: ["Market analysis", "Price theory", "Economic modeling"] },
          { title: "Macroeconomics", code: "EC201", duration: "1 Semester", credits: 3, description: "GDP, inflation, monetary policy, and fiscal policy.", skills: ["Economic indicators", "Policy analysis", "Forecasting"] },
          { title: "Econometrics", code: "EC301", duration: "1 Semester", credits: 4, description: "Statistical methods for economic data analysis.", skills: ["EViews", "Stata", "Regression analysis"] },
          { title: "International Economics", code: "EC401", duration: "1 Semester", credits: 3, description: "Trade theory, exchange rates, and global economic policy.", skills: ["Trade analysis", "Currency markets", "Global policy"] },
        ],
      },
    ],
  },
  {
    id: "science",
    name: "Faculty of Science",
    icon: Atom,
    color: "bg-purple-100 text-purple-600",
    description: "Physics, chemistry, mathematics, and biochemistry fundamentals.",
    departments: [
      {
        id: "math",
        name: "Mathematics & Actuarial Science",
        degree: "Bachelor of Science in Mathematics",
        courses: [
          { title: "Calculus I & II", code: "MATH101", duration: "1 Semester", credits: 4, description: "Limits, derivatives, integrals, and series.", skills: ["Differentiation", "Integration", "Mathematical modeling"] },
          { title: "Linear Algebra", code: "MATH201", duration: "1 Semester", credits: 3, description: "Vectors, matrices, eigenvalues, and vector spaces.", skills: ["Matrix operations", "Eigenanalysis", "MATLAB"] },
          { title: "Probability & Statistics", code: "MATH301", duration: "1 Semester", credits: 4, description: "Distributions, hypothesis testing, and regression.", skills: ["Statistical analysis", "R/Python", "Data modeling"] },
          { title: "Actuarial Science", code: "MATH401", duration: "1 Semester", credits: 4, description: "Risk assessment, financial modeling, and insurance mathematics.", skills: ["Risk modeling", "Excel/R", "Financial forecasting"] },
        ],
      },
      {
        id: "physics",
        name: "Physics",
        degree: "Bachelor of Science in Physics",
        courses: [
          { title: "Classical Mechanics", code: "PHY101", duration: "1 Semester", credits: 4, description: "Newtonian mechanics, energy, and momentum.", skills: ["Problem solving", "Lab experimentation", "MATLAB"] },
          { title: "Electromagnetism", code: "PHY201", duration: "1 Semester", credits: 4, description: "Electric and magnetic fields, Maxwell's equations.", skills: ["Field theory", "Circuit analysis", "Simulation"] },
          { title: "Quantum Mechanics", code: "PHY301", duration: "1 Semester", credits: 4, description: "Wave functions, Schrodinger equation, and quantum states.", skills: ["Quantum theory", "Computational physics", "Research"] },
        ],
      },
      {
        id: "chemistry",
        name: "Chemistry & Biochemistry",
        degree: "Bachelor of Science in Chemistry",
        courses: [
          { title: "General Chemistry", code: "CHE101", duration: "1 Semester", credits: 4, description: "Atomic structure, bonding, and chemical reactions.", skills: ["Lab techniques", "Chemical equations", "Safety protocols"] },
          { title: "Organic Chemistry", code: "CHE201", duration: "1 Semester", credits: 4, description: "Carbon compounds, reactions, and synthesis.", skills: ["Synthesis", "Spectroscopy", "Lab analysis"] },
          { title: "Biochemistry", code: "BCH301", duration: "1 Semester", credits: 4, description: "Proteins, enzymes, metabolism, and molecular biology.", skills: ["Enzyme assays", "Protein analysis", "Molecular biology"] },
        ],
      },
    ],
  },
  {
    id: "arts",
    name: "Faculty of Arts & Humanities",
    icon: Pencil,
    color: "bg-rose-100 text-rose-600",
    description: "Literature, languages, philosophy, history, and cultural studies.",
    departments: [
      {
        id: "lit",
        name: "Literature & Languages",
        degree: "Bachelor of Arts in Literature",
        courses: [
          { title: "Introduction to Literature", code: "LIT101", duration: "1 Semester", credits: 3, description: "Fiction, poetry, drama, and literary analysis.", skills: ["Critical analysis", "Essay writing", "Literary theory"] },
          { title: "African Literature", code: "LIT201", duration: "1 Semester", credits: 3, description: "Major works and movements in African literary tradition.", skills: ["Cultural analysis", "Comparative literature", "Research"] },
          { title: "Creative Writing", code: "LIT301", duration: "1 Semester", credits: 3, description: "Fiction, poetry, and non-fiction writing workshops.", skills: ["Writing craft", "Editing", "Publishing"] },
        ],
      },
      {
        id: "phil",
        name: "Philosophy & History",
        degree: "Bachelor of Arts in Philosophy",
        courses: [
          { title: "Introduction to Philosophy", code: "PHI101", duration: "1 Semester", credits: 3, description: "Major philosophical questions, ethics, and logic.", skills: ["Critical thinking", "Logic", "Ethical reasoning"] },
          { title: "African History", code: "HIS201", duration: "1 Semester", credits: 3, description: "Pre-colonial, colonial, and post-colonial African history.", skills: ["Historical analysis", "Research methods", "Source evaluation"] },
          { title: "Political Philosophy", code: "PHI301", duration: "1 Semester", credits: 3, description: "Justice, rights, democracy, and governance theories.", skills: ["Political theory", "Policy analysis", "Debate"] },
        ],
      },
    ],
  },
  {
    id: "social",
    name: "Faculty of Social Sciences",
    icon: Users,
    color: "bg-cyan-100 text-cyan-600",
    description: "Sociology, psychology, political science, and human behavior.",
    departments: [
      {
        id: "psych",
        name: "Psychology",
        degree: "Bachelor of Science in Psychology",
        courses: [
          { title: "Introduction to Psychology", code: "PSY101", duration: "1 Semester", credits: 3, description: "Human behavior, cognition, and mental processes.", skills: ["Behavioral analysis", "Research methods", "Psychometrics"] },
          { title: "Developmental Psychology", code: "PSY201", duration: "1 Semester", credits: 3, description: "Human growth and development across the lifespan.", skills: ["Child assessment", "Developmental theory", "Observation"] },
          { title: "Abnormal Psychology", code: "PSY301", duration: "1 Semester", credits: 3, description: "Psychological disorders, diagnosis, and treatment.", skills: ["Diagnosis", "CBT techniques", "Case studies"] },
        ],
      },
      {
        id: "socio",
        name: "Sociology & Political Science",
        degree: "Bachelor of Arts in Sociology",
        courses: [
          { title: "Introduction to Sociology", code: "SOC101", duration: "1 Semester", credits: 3, description: "Social structures, institutions, and change.", skills: ["Social analysis", "Research methods", "Survey design"] },
          { title: "Gender & Development Studies", code: "GDS201", duration: "1 Semester", credits: 3, description: "Gender roles, equity, and development policy.", skills: ["Gender analysis", "Policy review", "Community development"] },
          { title: "Public Administration", code: "PAD301", duration: "1 Semester", credits: 3, description: "Government structure, policy implementation, and governance.", skills: ["Policy analysis", "Public management", "Governance"] },
        ],
      },
    ],
  },
  {
    id: "education",
    name: "Faculty of Education",
    icon: GraduationCap,
    color: "bg-indigo-100 text-indigo-600",
    description: "Teaching methods, curriculum design, and educational leadership.",
    departments: [
      {
        id: "edadmin",
        name: "Educational Management & Curriculum",
        degree: "Bachelor of Education",
        courses: [
          { title: "Foundations of Education", code: "EDU101", duration: "1 Semester", credits: 3, description: "Education philosophy, history, and current trends.", skills: ["Teaching theories", "Classroom management", "Lesson planning"] },
          { title: "Curriculum Design & Instruction", code: "EDU201", duration: "1 Semester", credits: 4, description: "Designing curricula, assessment methods, and instructional strategies.", skills: ["Curriculum development", "Assessment design", "Differentiated instruction"] },
          { title: "Educational Psychology", code: "EDU301", duration: "1 Semester", credits: 3, description: "Learning theories, motivation, and cognitive development.", skills: ["Learning assessment", "Student motivation", "Cognitive science"] },
          { title: "Special Needs Education", code: "EDU401", duration: "1 Semester", credits: 3, description: "Inclusive education, disability support, and adaptive learning.", skills: ["IEP development", "Assistive technology", "Inclusive practices"] },
        ],
      },
    ],
  },
  {
    id: "health",
    name: "Faculty of Health Sciences",
    icon: Heart,
    color: "bg-red-100 text-red-600",
    description: "Anatomy, nursing, medical laboratory sciences, and public health.",
    departments: [
      {
        id: "nursing",
        name: "Nursing & Allied Health",
        degree: "Bachelor of Science in Nursing",
        courses: [
          { title: "Human Anatomy & Physiology", code: "HS101", duration: "1 Semester", credits: 4, description: "Body systems, structure, and function.", skills: ["Anatomical knowledge", "Lab skills", "Clinical observation"] },
          { title: "Fundamentals of Nursing", code: "HS201", duration: "1 Semester", credits: 4, description: "Patient care, hygiene, and basic nursing procedures.", skills: ["Patient care", "Vital signs", "Wound management"] },
          { title: "Community Health Nursing", code: "HS301", duration: "1 Semester", credits: 3, description: "Public health, disease prevention, and community outreach.", skills: ["Health education", "Epidemiology", "Community outreach"] },
          { title: "Medical Laboratory Sciences", code: "MLS301", duration: "1 Semester", credits: 4, description: "Lab techniques, hematology, and clinical chemistry.", skills: ["Lab procedures", "Microscopy", "Sample analysis"] },
        ],
      },
    ],
  },
  {
    id: "law",
    name: "Faculty of Law",
    icon: Scale,
    color: "bg-yellow-100 text-yellow-600",
    description: "Public law, private law, constitutional law, and international law.",
    departments: [
      {
        id: "lawdep",
        name: "Law & Legal Studies",
        degree: "Bachelor of Laws (LLB)",
        courses: [
          { title: "Introduction to Legal Systems", code: "LAW101", duration: "1 Semester", credits: 3, description: "Sources of law, court systems, and legal reasoning.", skills: ["Legal research", "Case analysis", "Legal writing"] },
          { title: "Constitutional Law", code: "LAW201", duration: "1 Semester", credits: 4, description: "Constitutional principles, rights, and governance.", skills: ["Constitutional analysis", "Rights litigation", "Judicial review"] },
          { title: "Criminal Law", code: "LAW301", duration: "1 Semester", credits: 4, description: "Criminal offenses, defenses, and procedure.", skills: ["Criminal analysis", "Evidence law", "Trial procedure"] },
          { title: "International Law", code: "LAW401", duration: "1 Semester", credits: 3, description: "Treaties, human rights, and international dispute resolution.", skills: ["Treaty analysis", "Human rights law", "Diplomatic law"] },
        ],
      },
    ],
  },
  {
    id: "architecture",
    name: "Faculty of Architecture & Design",
    icon: Palette,
    color: "bg-orange-100 text-orange-600",
    description: "Architecture, interior design, construction management, and fine arts.",
    departments: [
      {
        id: "arch",
        name: "Architecture & Interior Design",
        degree: "Bachelor of Architecture",
        courses: [
          { title: "Architectural Design Studio I", code: "ARD101", duration: "1 Semester", credits: 4, description: "Design principles, spatial composition, and sketching.", skills: ["AutoCAD", "SketchUp", "Design thinking"] },
          { title: "Building Materials & Construction", code: "ARD201", duration: "1 Semester", credits: 4, description: "Materials science, structural systems, and construction methods.", skills: ["Material testing", "Construction drawing", "Site analysis"] },
          { title: "Interior Design Principles", code: "INT201", duration: "1 Semester", credits: 3, description: "Space planning, color theory, and furniture design.", skills: ["3ds Max", "Color theory", "Space planning"] },
          { title: "Sustainable Architecture", code: "ARD401", duration: "1 Semester", credits: 3, description: "Green building design, energy efficiency, and LEED principles.", skills: ["Green design", "Energy modeling", "LEED certification"] },
        ],
      },
    ],
  },
  {
    id: "agriculture",
    name: "Faculty of Agriculture",
    icon: TreePine,
    color: "bg-lime-100 text-lime-600",
    description: "Agricultural economics, animal science, and plant science.",
    departments: [
      {
        id: "agri",
        name: "Agricultural Sciences",
        degree: "Bachelor of Science in Agriculture",
        courses: [
          { title: "Agricultural Economics", code: "AGR101", duration: "1 Semester", credits: 3, description: "Farm management, agricultural markets, and policy.", skills: ["Farm planning", "Market analysis", "Agribusiness"] },
          { title: "Animal Science", code: "AGR201", duration: "1 Semester", credits: 4, description: "Livestock management, nutrition, and breeding.", skills: ["Livestock care", "Feed formulation", "Breeding programs"] },
          { title: "Crop Science", code: "AGR301", duration: "1 Semester", credits: 4, description: "Crop production, soil science, and pest management.", skills: ["Soil analysis", "Pest control", "Irrigation"] },
          { title: "Food Science & Technology", code: "AGR401", duration: "1 Semester", credits: 4, description: "Food processing, preservation, and quality control.", skills: ["Food processing", "Quality assurance", "HACCP"] },
        ],
      },
    ],
  },
  {
    id: "environment",
    name: "Faculty of Environmental Studies",
    icon: Leaf,
    color: "bg-emerald-100 text-emerald-600",
    description: "Environmental science, sustainability, and planning.",
    departments: [
      {
        id: "env",
        name: "Environmental Science & Planning",
        degree: "Bachelor of Environmental Studies",
        courses: [
          { title: "Environmental Science", code: "ENV101", duration: "1 Semester", credits: 3, description: "Ecosystems, biodiversity, and environmental impact.", skills: ["Environmental assessment", "GIS", "Field research"] },
          { title: "Sustainability Studies", code: "ENV201", duration: "1 Semester", credits: 3, description: "Sustainable development, climate change, and resource management.", skills: ["Sustainability reporting", "Carbon auditing", "Policy analysis"] },
          { title: "Environmental Planning & Management", code: "ENV301", duration: "1 Semester", credits: 4, description: "Land use planning, environmental law, and impact assessment.", skills: ["EIA", "Urban planning", "Regulatory compliance"] },
        ],
      },
    ],
  },
  {
    id: "media",
    name: "Faculty of Communication & Media Studies",
    icon: Radio,
    color: "bg-pink-100 text-pink-600",
    description: "Journalism, film, theatre, and public relations.",
    departments: [
      {
        id: "media",
        name: "Media & Communication",
        degree: "Bachelor of Arts in Communication",
        courses: [
          { title: "Introduction to Mass Communication", code: "COM101", duration: "1 Semester", credits: 3, description: "Media theories, news writing, and communication models.", skills: ["News writing", "Media analysis", "Press ethics"] },
          { title: "Journalism & Digital Media", code: "COM201", duration: "1 Semester", credits: 3, description: "Reporting, editing, and digital storytelling.", skills: ["News reporting", "Video editing", "Social media"] },
          { title: "Public Relations & Advertising", code: "COM301", duration: "1 Semester", credits: 3, description: "PR campaigns, brand communication, and crisis management.", skills: ["Campaign design", "Crisis PR", "Media relations"] },
          { title: "Film & Theatre Studies", code: "COM401", duration: "1 Semester", credits: 3, description: "Film production, screenwriting, and performance.", skills: ["Filmmaking", "Screenwriting", "Directing"] },
        ],
      },
    ],
  },
  {
    id: "tourism",
    name: "Faculty of Tourism & Hospitality",
    icon: Globe,
    color: "bg-teal-100 text-teal-600",
    description: "Hospitality management, tourism, and recreation.",
    departments: [
      {
        id: "tourism",
        name: "Hospitality & Tourism Management",
        degree: "Bachelor of Science in Tourism Management",
        courses: [
          { title: "Introduction to Hospitality", code: "THM101", duration: "1 Semester", credits: 3, description: "Hotel operations, food service, and guest relations.", skills: ["Hotel operations", "Guest service", "Food safety"] },
          { title: "Tourism Planning & Development", code: "THM201", duration: "1 Semester", credits: 3, description: "Tourism policy, destination marketing, and sustainability.", skills: ["Destination planning", "Marketing", "Sustainability"] },
          { title: "Recreation & Sports Management", code: "THM301", duration: "1 Semester", credits: 3, description: "Event management, sports tourism, and recreation planning.", skills: ["Event planning", "Sports marketing", "Recreation design"] },
        ],
      },
    ],
  },
  {
    id: "library",
    name: "Faculty of Library & Information Science",
    icon: BookOpenCheck,
    color: "bg-violet-100 text-violet-600",
    description: "Library studies, archival science, and information management.",
    departments: [
      {
        id: "lis",
        name: "Library & Information Science",
        degree: "Bachelor of Library & Information Science",
        courses: [
          { title: "Foundations of Library Science", code: "LIS101", duration: "1 Semester", credits: 3, description: "Library organization, cataloging, and classification.", skills: ["Cataloging", "Dewey system", "Database management"] },
          { title: "Information Management & Digital Libraries", code: "LIS201", duration: "1 Semester", credits: 3, description: "Digital archiving, metadata, and information systems.", skills: ["Digital archiving", "Metadata standards", "Repository management"] },
          { title: "Research Methods for Information Science", code: "LIS301", duration: "1 Semester", credits: 3, description: "Research design, data collection, and analysis.", skills: ["Survey design", "Data analysis", "Academic writing"] },
        ],
      },
    ],
  },
  {
    id: "sports",
    name: "Faculty of Physical Education & Sports Science",
    icon: Dumbbell,
    color: "bg-sky-100 text-sky-600",
    description: "Kinesiology, exercise science, and sports coaching.",
    departments: [
      {
        id: "sports",
        name: "Sports & Exercise Science",
        degree: "Bachelor of Science in Sports Science",
        courses: [
          { title: "Kinesiology & Biomechanics", code: "SES101", duration: "1 Semester", credits: 4, description: "Human movement analysis and sports biomechanics.", skills: ["Motion analysis", "Anatomy", "Lab testing"] },
          { title: "Exercise Physiology", code: "SES201", duration: "1 Semester", credits: 4, description: "Body response to exercise, training principles, and nutrition.", skills: ["Fitness testing", "Training programs", "Sports nutrition"] },
          { title: "Sports Coaching & Management", code: "SES301", duration: "1 Semester", credits: 3, description: "Coaching methods, team management, and sports psychology.", skills: ["Coaching techniques", "Team leadership", "Performance analysis"] },
        ],
      },
    ],
  },
];

export function findCourse(departmentId?: string | null, courseCode?: string | null) {
  for (const f of faculties) {
    const dept = f.departments.find((d) => d.id === departmentId);
    if (dept) {
      const course = courseCode ? dept.courses.find((c) => c.code === courseCode) : undefined;
      if (course) return { faculty: f, department: dept, course };
      if (!courseCode) return { faculty: f, department: dept, course: null };
    }
  }
  if (courseCode) {
    for (const f of faculties) {
      for (const d of f.departments) {
        const course = d.courses.find((c) => c.code === courseCode);
        if (course) return { faculty: f, department: d, course };
      }
    }
  }
  return null;
}
