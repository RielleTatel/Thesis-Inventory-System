/* ============================================================
   Public screens: Login, Browse/Search, Thesis Detail
   ============================================================ */

/* ---------- 1. LOGIN ---------- */
function LoginScreen({ onLogin, onPublic }) {
  const [email, setEmail] = React.useState("");
  const [pw, setPw] = React.useState("");
  const [err, setErr] = React.useState("");

  const submit = (e) => {
    e.preventDefault();
    if (!email || !pw) { setErr("Enter your email and password to continue."); return; }
    onLogin(email.includes("admin") ? "admin" : "department");
  };

  return (
    <div style={{ minHeight: "100vh", background: "var(--navy)", display: "grid", placeItems: "center", padding: 24, position: "relative", overflow: "hidden" }}>
      {/* decorative cyan band */}
      <div style={{ position: "absolute", inset: 0, background: "radial-gradient(1100px 500px at 50% -10%, rgba(0,192,239,.22), transparent 60%)", pointerEvents: "none" }} />
      <div style={{ position: "relative", width: "100%", maxWidth: 410 }}>
        <div style={{ textAlign: "center", marginBottom: 22, color: "#fff" }}>
          <span className="brand-mark" style={{ width: 52, height: 52, margin: "0 auto 14px", display: "grid" }}>
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#02327C" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
              <path d="M4 5a2 2 0 0 1 2-2h12v16H6a2 2 0 0 0-2 2z" /><path d="M9 7h6M9 10h6" />
            </svg>
          </span>
          <div style={{ fontWeight: 700, fontSize: 27, letterSpacing: "-0.01em" }}>AdZU <span style={{ color: "var(--on-navy-dim)", fontWeight: 400 }}>Thesis Archives</span></div>
          <div style={{ color: "var(--on-navy-dim)", fontSize: 13.5, marginTop: 4 }}>Department &amp; Administrator sign-in</div>
        </div>

        <div className="card card-pad" style={{ padding: "26px 26px 24px" }}>
          <form onSubmit={submit} className="col" style={{ gap: 16 }}>
            <Field label="Email or username">
              <input className="input" type="text" placeholder="department@univ.edu" value={email}
                onChange={(e) => { setEmail(e.target.value); setErr(""); }} autoFocus />
            </Field>
            <Field label="Password" error={err}>
              <input className="input" type="password" placeholder="••••••••" value={pw}
                onChange={(e) => { setPw(e.target.value); setErr(""); }} />
            </Field>
            <div style={{ display: "flex", justifyContent: "flex-end", marginTop: -4 }}>
              <a className="navbar-link" style={{ color: "var(--navy)", padding: 0, fontSize: 13.5, whiteSpace: "nowrap" }} onClick={(e) => e.preventDefault()} href="#">Forgot password?</a>
            </div>
            <Btn variant="primary" type="submit" block>Login</Btn>
            <div style={{ display: "flex", alignItems: "center", gap: 12, color: "var(--fg-3)", fontSize: 12.5, fontWeight: 600 }}>
              <span style={{ flex: 1, height: 1, background: "var(--line)" }} /> OR <span style={{ flex: 1, height: 1, background: "var(--line)" }} />
            </div>
            <Btn variant="secondary" type="button" block onClick={() => onLogin("department")}>
              <GoogleG /> Sign in with Google
            </Btn>
          </form>
        </div>

        <div style={{ textAlign: "center", marginTop: 18 }}>
          <a className="navbar-link" style={{ color: "#fff", fontWeight: 600 }} href="#" onClick={(e) => { e.preventDefault(); onPublic(); }}>
            ← Browse theses as a visitor
          </a>
        </div>
        <div style={{ textAlign: "center", marginTop: 6, color: "var(--on-navy-dim)", fontSize: 12 }}>
          Tip: any email with “admin” signs in as Administrator.
        </div>
      </div>
    </div>
  );
}

/* ---------- Thesis result card ---------- */
function ThesisCard({ t, onOpen }) {
  return (
    <button className="card thesis-card" onClick={() => onOpen(t)}
      style={{ textAlign: "left", cursor: "pointer", padding: 0, display: "flex", flexDirection: "column", border: "1px solid var(--line)" }}>
      <div className="card-pad" style={{ padding: "18px 20px", display: "flex", flexDirection: "column", gap: 10, flex: 1 }}>
        <div style={{ display: "flex", gap: 8, alignItems: "center", flexWrap: "wrap" }}>
          <Badge tone="cyan">{t.year}</Badge>
          <span style={{ fontSize: 12.5, fontWeight: 600, color: "var(--fg-3)" }}>{t.program}</span>
        </div>
        <h3 style={{ margin: 0, fontSize: 17, fontWeight: 600, color: "var(--navy)", lineHeight: 1.3, textWrap: "pretty" }}>{t.title}</h3>
        <div style={{ fontSize: 13.5, color: "var(--fg-2)", fontWeight: 600 }}>{t.authors.join(", ")}</div>
        <p style={{ margin: 0, fontSize: 13.5, color: "var(--fg-2)", lineHeight: 1.5, display: "-webkit-box", WebkitLineClamp: 3, WebkitBoxOrient: "vertical", overflow: "hidden" }}>{t.abstract}</p>
        <div className="chip-row" style={{ marginTop: "auto", paddingTop: 4 }}>
          {t.keywords.slice(0, 3).map((k, i) => <Chip key={i} kind="key">{k}</Chip>)}
          {t.keywords.length > 3 && <span className="chip" style={{ background: "transparent", border: "none", color: "var(--fg-3)" }}>+{t.keywords.length - 3}</span>}
        </div>
      </div>
    </button>
  );
}

/* ---------- 2. PUBLIC BROWSE / SEARCH ---------- */
function BrowseScreen({ onOpen }) {
  const [q, setQ] = React.useState("");
  const [yearFrom, setYearFrom] = React.useState("");
  const [yearTo, setYearTo] = React.useState("");
  const [prog, setProg] = React.useState("");
  const [kw, setKw] = React.useState("");

  const years = [...new Set(THESES.map((t) => t.year))].sort((a, b) => b - a);
  const progs = [...new Set(THESES.map((t) => t.program))].sort();
  const kws = [...new Set(THESES.flatMap((t) => t.keywords))].sort();

  const results = THESES.filter((t) => {
    const hay = (t.title + " " + t.authors.join(" ") + " " + t.abstract + " " + t.keywords.join(" ")).toLowerCase();
    return (!q || hay.includes(q.toLowerCase())) &&
      (!yearFrom || t.year >= +yearFrom) &&
      (!yearTo || t.year <= +yearTo) &&
      (!prog || t.program === prog) &&
      (!kw || t.keywords.includes(kw));
  });

  const clear = () => { setQ(""); setYearFrom(""); setYearTo(""); setProg(""); setKw(""); };
  const yearError = yearFrom && yearTo && +yearFrom > +yearTo;
  const hasFilters = q || yearFrom || yearTo || prog || kw;

  return (
    <div className="public-main">
      {/* Hero search */}
      <div style={{ background: "linear-gradient(180deg, #02327C, #022a68)", padding: "46px 24px 40px", position: "relative", overflow: "hidden" }}>
        <div style={{ position: "absolute", inset: 0, background: "radial-gradient(700px 280px at 80% 0%, rgba(0,192,239,.20), transparent 60%)" }} />
        <div style={{ position: "relative", maxWidth: 760, margin: "0 auto", textAlign: "center", color: "#fff" }}>
          <h1 style={{ margin: 0, fontSize: 33, fontWeight: 700, letterSpacing: "-0.01em" }}>Search the thesis archive</h1>
          <p style={{ margin: "10px 0 22px", color: "var(--on-navy-dim)", fontSize: 16 }}>Browse {THESES.length} catalogued theses across every college — free and open to the public.</p>
          <div className="searchbar" style={{ maxWidth: 620, margin: "0 auto" }}>
            <span style={{ position: "absolute", left: 16, top: "50%", transform: "translateY(-50%)", color: "var(--fg-3)" }}><Icon name="search" size={20} /></span>
            <input className="input" style={{ padding: "15px 16px 15px 48px", fontSize: 16, background: "#fff", border: "none", boxShadow: "var(--shadow-md)", borderRadius: 10 }}
              placeholder="Search by title, author, abstract, or keyword…" value={q} onChange={(e) => setQ(e.target.value)} />
          </div>
        </div>
      </div>

      <div className="content-wrap" style={{ padding: "26px 24px 64px" }}>
        {/* Filters */}
        <div className="card card-pad" style={{ padding: "16px 18px", marginBottom: 22, display: "flex", gap: 14, alignItems: "flex-end", flexWrap: "wrap" }}>
          <div style={{ display: "flex", alignItems: "center", gap: 8, color: "var(--fg-2)", fontWeight: 700, fontSize: 13, alignSelf: "center" }}>
            <Icon name="filter" size={16} /> Filters
          </div>
          <Field label="Year range" error={yearError ? "From year can't be after To year." : null}>
            <div style={{ display: "flex", alignItems: "center", gap: 8 }}>
              <select className={`select${yearError ? " invalid" : ""}`} style={{ minWidth: 104 }} value={yearFrom} onChange={(e) => setYearFrom(e.target.value)} aria-label="From year">
                <option value="">From</option>{years.map((y) => <option key={y} value={y}>{y}</option>)}
              </select>
              <span style={{ color: "var(--fg-3)", fontWeight: 700 }}>–</span>
              <select className={`select${yearError ? " invalid" : ""}`} style={{ minWidth: 104 }} value={yearTo} onChange={(e) => setYearTo(e.target.value)} aria-label="To year">
                <option value="">To</option>{years.map((y) => <option key={y} value={y}>{y}</option>)}
              </select>
            </div>
          </Field>
          <Field label="Program / Department"><select className="select" style={{ minWidth: 220 }} value={prog} onChange={(e) => setProg(e.target.value)}><option value="">All programs</option>{progs.map((p) => <option key={p} value={p}>{p}</option>)}</select></Field>
          <Field label="Keyword"><select className="select" style={{ minWidth: 180 }} value={kw} onChange={(e) => setKw(e.target.value)}><option value="">All keywords</option>{kws.map((k) => <option key={k} value={k}>{k}</option>)}</select></Field>
          {hasFilters && <Btn variant="ghost" onClick={clear} icon="x">Clear</Btn>}
          <div style={{ marginLeft: "auto", alignSelf: "center", color: "var(--fg-2)", fontSize: 13.5, fontWeight: 600 }}>
            {results.length} {results.length === 1 ? "result" : "results"}
          </div>
        </div>

        {/* Results */}
        {results.length > 0 ? (
          <div style={{ display: "grid", gridTemplateColumns: "repeat(auto-fill, minmax(320px, 1fr))", gap: 18 }}>
            {results.map((t) => <ThesisCard key={t.id} t={t} onOpen={onOpen} />)}
          </div>
        ) : (
          <div className="card"><div className="empty">
            <div className="empty-mark"><Icon name="search" size={30} /></div>
            <h3>No theses match your search</h3>
            <p>Try removing a filter or searching a broader term. The archive may not yet contain a record for this topic.</p>
            <div style={{ marginTop: 18 }}><Btn variant="secondary" onClick={clear} icon="x">Clear all filters</Btn></div>
          </div></div>
        )}
      </div>
    </div>
  );
}

/* ---------- 3. THESIS DETAIL ---------- */
function DetailRow({ label, children }) {
  return (
    <div style={{ display: "grid", gridTemplateColumns: "180px 1fr", gap: 20, padding: "18px 0", borderBottom: "1px solid var(--line)" }} className="detail-row">
      <div style={{ fontSize: 12.5, fontWeight: 700, letterSpacing: ".05em", textTransform: "uppercase", color: "var(--fg-3)", paddingTop: 2 }}>{label}</div>
      <div>{children}</div>
    </div>
  );
}

function DetailScreen({ t, onBack }) {
  return (
    <div className="public-main">
      <div className="content-wrap" style={{ padding: "26px 24px 70px", maxWidth: 900 }}>
        <button className="btn btn-ghost btn-sm" onClick={onBack} style={{ marginBottom: 18, paddingLeft: 6 }}><Icon name="back" size={16} /> Back to results</button>

        <div className="card" style={{ overflow: "hidden" }}>
          <div style={{ height: 6, background: "var(--cyan)" }} />
          <div className="card-pad" style={{ padding: "30px 34px 34px" }}>
            <div style={{ display: "flex", gap: 10, alignItems: "center", marginBottom: 16, flexWrap: "wrap" }}>
              <Badge tone="cyan">{t.year}</Badge>
              <span style={{ fontSize: 13.5, fontWeight: 600, color: "var(--fg-2)" }}>{t.program}</span>
              <span style={{ color: "var(--line-strong)" }}>•</span>
              <span style={{ fontSize: 13.5, color: "var(--fg-3)" }}>{t.department}</span>
            </div>
            <h1 style={{ margin: "0 0 18px", fontSize: 30, fontWeight: 700, lineHeight: 1.2, letterSpacing: "-0.01em", color: "var(--navy)", textWrap: "pretty" }}>{t.title}</h1>

            <div style={{ marginBottom: 6 }}>
              <div style={{ fontSize: 12.5, fontWeight: 700, letterSpacing: ".05em", textTransform: "uppercase", color: "var(--fg-3)", marginBottom: 9 }}>Authors</div>
              <ChipRow items={t.authors} kind="person" />
            </div>

            <div style={{ marginTop: 28 }}>
              <div className="sec-label">Abstract</div>
              <p style={{ margin: 0, fontSize: 16, lineHeight: 1.65, color: "var(--fg)", textWrap: "pretty" }}>{t.abstract}</p>
            </div>

            <div style={{ marginTop: 28 }}>
              <div className="sec-label">Recommendations</div>
              <p style={{ margin: 0, fontSize: 16, lineHeight: 1.65, color: "var(--fg)", textWrap: "pretty" }}>{t.recommendations}</p>
            </div>

            <div style={{ marginTop: 30 }}>
              <DetailRow label="Adviser"><ChipRow items={t.advisers} kind="person" /></DetailRow>
              <DetailRow label="Panelists"><ChipRow items={t.panelists} kind="person" /></DetailRow>
              <DetailRow label="Keywords"><ChipRow items={t.keywords} kind="key" /></DetailRow>
              <div style={{ display: "grid", gridTemplateColumns: "180px 1fr", gap: 20, padding: "18px 0" }} className="detail-row">
                <div style={{ fontSize: 12.5, fontWeight: 700, letterSpacing: ".05em", textTransform: "uppercase", color: "var(--fg-3)" }}>Program</div>
                <div style={{ fontSize: 15, color: "var(--fg)" }}>{t.program} — {t.department}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

Object.assign(window, { LoginScreen, BrowseScreen, DetailScreen, ThesisCard });
