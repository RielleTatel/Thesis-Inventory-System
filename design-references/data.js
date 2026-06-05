/* ============================================================
   Sample data for the Thesis Inventory prototype
   ============================================================ */

window.THESES = [
  {
    id: "t1",
    title: "Solar-Powered Aquaponics for Urban Rooftop Food Security",
    authors: ["Marisol D. Venancio", "Karl Andre P. Bautista"],
    year: 2024,
    program: "BS Environmental Science",
    department: "College of Science",
    abstract:
      "This study designed and evaluated a modular solar-powered aquaponics unit suited to dense urban rooftops. Across a six-month deployment, the system sustained tilapia and leafy-green production with a 38% reduction in municipal water use compared to conventional rooftop gardens, while operating entirely off-grid through a 1.2 kW photovoltaic array.",
    recommendations:
      "Future iterations should integrate low-cost dissolved-oxygen telemetry to pre-empt fish stress events, and a community pilot across three barangays is recommended to assess adoption barriers and shared-maintenance models.",
    advisers: ["Dr. Elena R. Marquez"],
    panelists: ["Dr. Jose P. Antonio", "Engr. Lila S. Cruz", "Dr. Ramon T. delos Reyes"],
    keywords: ["aquaponics", "renewable energy", "urban agriculture", "food security", "sustainability"],
    owner: "envsci",
    updated: "2026-05-28",
  },
  {
    id: "t2",
    title: "A Tagalog–English Code-Switching Corpus for Low-Resource Speech Recognition",
    authors: ["Patricia Anne L. Soriano", "Miguel V. Tan", "Reza F. Hidalgo"],
    year: 2025,
    program: "BS Computer Science",
    department: "College of Computing",
    abstract:
      "We present a 42-hour annotated corpus of conversational Tagalog–English code-switching and benchmark three end-to-end ASR architectures against it. Fine-tuning a multilingual transformer on the corpus reduced word error rate by 21.4% over a Tagalog-only baseline, with the largest gains on intra-sentential switch boundaries.",
    recommendations:
      "We recommend expanding the corpus to include regional varieties (Cebuano, Ilokano) and releasing speaker-anonymized audio under an open license to support reproducible low-resource speech research.",
    advisers: ["Dr. Anton G. Reyes", "Dr. Carmela B. Lim"],
    panelists: ["Dr. Felix M. Ong", "Engr. Dianne C. Salazar"],
    keywords: ["speech recognition", "code-switching", "NLP", "low-resource", "corpus"],
    owner: "cs",
    updated: "2026-05-30",
  },
  {
    id: "t3",
    title: "Microfinance Repayment Behavior Among Women-Led Sari-Sari Stores",
    authors: ["Joanna Mae R. Villaluz"],
    year: 2023,
    program: "BS Business Administration",
    department: "College of Business",
    abstract:
      "Using a panel of 312 women-led microenterprises, this thesis examines how group-lending structures and mobile-money disbursement affect on-time repayment. Mobile disbursement was associated with a 14-point increase in repayment rates, mediated largely by reduced travel time to lending centers.",
    recommendations:
      "Lending institutions should prioritize mobile-money rails for last-mile borrowers and pair them with brief financial-literacy nudges delivered via SMS at repayment milestones.",
    advisers: ["Dr. Teresita V. Aquino"],
    panelists: ["Dr. Manuel R. Garcia", "Prof. Bianca L. Reyes", "Dr. Oscar D. Mendoza"],
    keywords: ["microfinance", "financial inclusion", "mobile money", "gender", "MSME"],
    owner: "business",
    updated: "2026-04-19",
  },
  {
    id: "t4",
    title: "Bamboo-Reinforced Concrete Panels for Low-Cost Disaster-Resilient Housing",
    authors: ["Nathaniel C. Ramos", "Aira Joy M. Pascual"],
    year: 2025,
    program: "BS Civil Engineering",
    department: "College of Engineering",
    abstract:
      "This research evaluated the flexural and seismic performance of treated-bamboo-reinforced concrete wall panels as an alternative to steel rebar in single-story housing. Treated bamboo specimens reached 71% of the load capacity of equivalent steel-reinforced panels at one-fifth the embodied carbon.",
    recommendations:
      "Standardized borate-treatment protocols and accelerated weathering trials are recommended before field adoption, alongside a cost study factoring local bamboo supply chains.",
    advisers: ["Engr. Rodel S. Bautista"],
    panelists: ["Dr. Henry T. Lopez", "Engr. Marivic D. Santos"],
    keywords: ["bamboo", "sustainable construction", "disaster resilience", "low-cost housing", "materials"],
    owner: "engineering",
    updated: "2026-05-12",
  },
  {
    id: "t5",
    title: "Sentiment Dynamics of Disaster Response on Philippine Social Media",
    authors: ["Christian Earl B. Navarro", "Yuki S. Fernandez"],
    year: 2024,
    program: "BS Information Technology",
    department: "College of Computing",
    abstract:
      "We analyzed 1.8 million geotagged posts across three typhoon events to model how public sentiment shifts between warning, impact, and recovery phases. A phase-aware classifier identified actionable need signals (rescue, relief, medical) with 0.88 F1, surfacing them a median of four hours before formal situation reports.",
    recommendations:
      "Disaster agencies should pilot the classifier as a triage aid rather than a sole source, with a human-in-the-loop dashboard and clear handling of misinformation surges during peak impact.",
    advisers: ["Dr. Carmela B. Lim"],
    panelists: ["Dr. Anton G. Reyes", "Engr. Paolo M. Tan", "Dr. Sheila R. Uy"],
    keywords: ["social media", "disaster response", "sentiment analysis", "machine learning", "crisis informatics"],
    owner: "cs",
    updated: "2026-03-22",
  },
  {
    id: "t6",
    title: "Indigenous Weaving Motifs as Pedagogy for Geometry Instruction",
    authors: ["Lourdes A. Maglinte", "Diego R. Espinosa", "Hannah K. Lim"],
    year: 2023,
    program: "Bachelor of Secondary Education",
    department: "College of Education",
    abstract:
      "This mixed-methods study integrated Kalinga and Yakan weaving patterns into a Grade 9 geometry unit on symmetry and transformation. The culturally responsive unit improved conceptual post-test scores by 0.6 standard deviations and was associated with markedly higher reported engagement among learners.",
    recommendations:
      "Teacher-training modules and a vetted motif library are recommended so the approach can scale without misappropriating cultural designs; collaboration with weaving communities should be formalized.",
    advisers: ["Dr. Grace P. Villanueva"],
    panelists: ["Dr. Noel B. Castro", "Prof. Editha M. Lim"],
    keywords: ["mathematics education", "culturally responsive", "geometry", "indigenous knowledge", "pedagogy"],
    owner: "education",
    updated: "2026-02-08",
  },
];

window.ACCOUNTS = [
  { id: "a1", dept: "College of Computing", username: "computing@univ.edu", status: "active", created: "2024-08-12", records: 2 },
  { id: "a2", dept: "College of Engineering", username: "engineering@univ.edu", status: "active", created: "2024-08-12", records: 1 },
  { id: "a3", dept: "College of Science", username: "science@univ.edu", status: "active", created: "2024-09-03", records: 1 },
  { id: "a4", dept: "College of Business", username: "business@univ.edu", status: "active", created: "2025-01-21", records: 1 },
  { id: "a5", dept: "College of Education", username: "education@univ.edu", status: "inactive", created: "2025-01-21", records: 1 },
  { id: "a6", dept: "College of Arts & Letters", username: "artsletters@univ.edu", status: "active", created: "2025-06-30", records: 0 },
];

window.ACTIVITY = [
  { id: "l1", actor: "College of Computing", actorRole: "Department", action: "edited", type: "thesis", target: "Tagalog–English Code-Switching Corpus…", time: "2026-05-30 14:22" },
  { id: "l2", actor: "admin@univ.edu", actorRole: "Admin", action: "created", type: "account", target: "College of Arts & Letters", time: "2026-05-30 09:41" },
  { id: "l3", actor: "College of Science", actorRole: "Department", action: "created", type: "thesis", target: "Solar-Powered Aquaponics for Urban Rooftop…", time: "2026-05-28 16:08" },
  { id: "l4", actor: "admin@univ.edu", actorRole: "Admin", action: "deactivated", type: "account", target: "College of Education", time: "2026-05-27 11:15" },
  { id: "l5", actor: "College of Engineering", actorRole: "Department", action: "edited", type: "thesis", target: "Bamboo-Reinforced Concrete Panels…", time: "2026-05-12 10:03" },
  { id: "l6", actor: "College of Computing", actorRole: "Department", action: "deleted", type: "thesis", target: "Draft: Edge Caching Heuristics (untitled)", time: "2026-05-09 15:47" },
  { id: "l7", actor: "admin@univ.edu", actorRole: "Admin", action: "edited", type: "account", target: "College of Business", time: "2026-04-30 08:55" },
  { id: "l8", actor: "College of Business", actorRole: "Department", action: "created", type: "thesis", target: "Microfinance Repayment Behavior…", time: "2026-04-19 13:30" },
];

window.PROGRAMS = [
  "BS Computer Science",
  "BS Information Technology",
  "BS Environmental Science",
  "BS Civil Engineering",
  "BS Business Administration",
  "Bachelor of Secondary Education",
  "Bachelor of Arts in Communication",
];

window.OCR_SAMPLE = "This study designed and evaluated a modular solar-powered aquaponics unit suited to dense urban rooftops. Across a six-month deployment, the system sustained tilapia and leafy-green production with a 38% reduction in municipal water use compared to conventional rooftop gardens, while operating entirely off-grid through a 1.2 kW photovoltaic array.\n\n[Note: text captured from printed copy — review for OCR errors such as 'tllapia' or merged words before using.]";
