/* ============================================================
   App root — routing, roles, shell selection, prototype switcher
   ============================================================ */
const { useState, useEffect } = React;

const DEPT = { id: "computing", name: "College of Computing", username: "computing@univ.edu", initials: "CC" };
const ADMIN = { name: "System Admin", username: "admin@univ.edu", initials: "SA", role: "Administrator" };

function App() {
  // role: "viewer" | "department" | "admin"
  const [role, setRole] = useState("viewer");
  const [screen, setScreen] = useState("browse"); // current screen id
  const [selected, setSelected] = useState(null);  // thesis being viewed
  const [editing, setEditing] = useState(null);    // thesis being edited (or "new")
  const [scanField, setScanField] = useState(null);// OCR target field
  const [scanApply, setScanApply] = useState(null); // {field, text, n} applied into the form
  const [delThesis, setDelThesis] = useState(null);
  const [delAccount, setDelAccount] = useState(null);
  const [creatingAcct, setCreatingAcct] = useState(false);
  const [toast, setToast] = useState("");
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [protoOpen, setProtoOpen] = useState(false);

  const [theses, setTheses] = useState(window.THESES);
  const [accounts, setAccounts] = useState(window.ACCOUNTS);

  const flash = (m) => { setToast(m); clearTimeout(window.__t); window.__t = setTimeout(() => setToast(""), 2400); };

  // role -> default landing screen
  const land = (r) => {
    if (r === "viewer") return "browse";
    if (r === "department") return "dashboard";
    return "accounts";
  };
  const switchRole = (r) => { setRole(r); setScreen(land(r)); setSelected(null); setEditing(null); setSidebarOpen(false); };

  // when scan modal used
  const useScan = (field, text) => {
    setScanApply({ field, text, n: Date.now() });
    setScanField(null);
    flash(`Text added to ${field === "abstract" ? "Abstract" : "Recommendations"}.`);
  };

  const saveThesis = (data) => {
    if (data.id) {
      setTheses((ts) => ts.map((t) => t.id === data.id ? { ...data, updated: "2026-06-04" } : t));
      flash("Thesis updated.");
    } else {
      const nt = { ...data, id: "t" + Date.now(), owner: DEPT.id, department: DEPT.name, updated: "2026-06-04" };
      setTheses((ts) => [nt, ...ts]);
      flash("Thesis added.");
    }
    setEditing(null); setScreen("dashboard");
  };

  const deptTheses = theses.filter((t) => t.owner === DEPT.id || t.department === DEPT.name);

  /* ----- sidebar menus ----- */
  const deptMenu = [
    { section: "Department" },
    { id: "dashboard", label: "My theses", icon: "grid" },
    { id: "form", label: "Add thesis", icon: "plus" },
  ];
  const adminMenu = [
    { section: "Administration" },
    { id: "accounts", label: "Department accounts", icon: "users" },
    { id: "activity", label: "Activity log", icon: "activity" },
  ];

  /* ----- top-right navbar content ----- */
  const viewerLinks = (
    <>
      <a className={`navbar-link${screen === "browse" ? " active" : ""}`} onClick={() => { setScreen("browse"); setSelected(null); }}>Browse</a>
      <Btn variant="primary" size="sm" icon="lock" onClick={() => { setRole("auth"); setScreen("login"); }}>Sign in</Btn>
    </>
  );

  /* ===== RENDER: a thesis detail can show in any role over its context ===== */

  // Login screen (full-bleed, no shell)
  if (screen === "login") {
    return <LoginScreen
      onLogin={(r) => switchRole(r)}
      onPublic={() => switchRole("viewer")}
    />;
  }

  // VIEWER (public) — navbar only
  if (role === "viewer" || role === "auth") {
    return (
      <div className="app">
        <Navbar onBrand={() => { setScreen("browse"); setSelected(null); }} rightLinks={viewerLinks} />
        {selected
          ? <DetailScreen t={selected} onBack={() => setSelected(null)} />
          : <BrowseScreen onOpen={(t) => setSelected(t)} />}
        <ProtoSwitcher {...{ role, screen, selected, switchRole, setScreen, setSelected, protoOpen, setProtoOpen }} />
      </div>
    );
  }

  // LOGGED-IN (department / admin) — admin shell
  const user = role === "admin" ? ADMIN : { ...DEPT, role: "Department" };
  const menu = role === "admin" ? adminMenu : deptMenu;
  const onNav = (id) => {
    setSidebarOpen(false);
    if (id === "form") { setEditing(null); setScreen("form"); }
    else { setScreen(id); setSelected(null); setEditing(null); }
  };

  const logoutLink = (
    <a className="navbar-link" onClick={() => switchRole("viewer")}><Icon name="logout" size={16} /> &nbsp;Sign out</a>
  );

  return (
    <div className="app">
      <Navbar
        onBrand={() => onNav(menu[1].id)}
        rightLinks={logoutLink}
        user={user}
        onMenu={() => setSidebarOpen((o) => !o)}
      />
      <div className="shell">
        {sidebarOpen && <div className="sidebar-backdrop" onClick={() => setSidebarOpen(false)} />}
        <Sidebar
          items={menu}
          current={screen === "form" ? "form" : screen}
          onNav={onNav}
          open={sidebarOpen}
          footer={<>Signed in as<br /><strong style={{ color: "#fff" }}>{role === "admin" ? "Administrator" : DEPT.name}</strong></>}
        />
        <main className="shell-main">
          {/* Department */}
          {role === "department" && screen === "dashboard" && !selected &&
            <DashboardScreen deptName={DEPT.name} theses={deptTheses}
              onAdd={() => { setEditing(null); setScreen("form"); }}
              onEdit={(t) => { setEditing(t); setScreen("form"); }}
              onDelete={(t) => setDelThesis(t)}
              onOpen={(t) => setSelected(t)} />}

          {role === "department" && selected &&
            <div className="content-wrap"><button className="btn btn-ghost btn-sm" onClick={() => setSelected(null)} style={{ marginBottom: 16, paddingLeft: 6 }}><Icon name="back" size={16} /> Back to my theses</button>
              <DetailInline t={selected} /></div>}

          {role === "department" && screen === "form" &&
            <FormScreen initial={editing} scanApply={scanApply}
              onCancel={() => { setEditing(null); setScreen("dashboard"); }}
              onSave={saveThesis}
              onScan={(field) => setScanField(field)} />}

          {/* Admin */}
          {role === "admin" && screen === "accounts" &&
            <AccountsScreen accounts={accounts}
              onCreate={() => setCreatingAcct(true)}
              onDeleteAccount={(a) => setDelAccount(a)}
              onToast={flash} />}

          {role === "admin" && screen === "activity" &&
            <ActivityScreen log={window.ACTIVITY} />}
        </main>
      </div>

      {/* Modals */}
      {scanField && <OcrModal field={scanField} onClose={() => setScanField(null)} onUse={useScan} />}
      {delThesis && <DeleteThesisModal thesis={delThesis} onClose={() => setDelThesis(null)}
        onConfirm={() => { setTheses((ts) => ts.filter((t) => t.id !== delThesis.id)); setDelThesis(null); flash("Thesis deleted."); }} />}
      {delAccount && <DeleteAccountModal account={delAccount} onClose={() => setDelAccount(null)}
        onConfirm={(mode) => { setAccounts((as) => as.filter((a) => a.id !== delAccount.id)); setDelAccount(null); flash(mode === "delete" ? "Account and records deleted." : "Account deleted, records kept."); }} />}
      {creatingAcct && <CreateAccountModal onClose={() => setCreatingAcct(false)}
        onCreate={({ dept, email }) => { setAccounts((as) => [{ id: "a" + Date.now(), dept, username: email, status: "active", created: "2026-06-04", records: 0 }, ...as]); setCreatingAcct(false); flash("Department account created."); }} />}

      <Toast message={toast} />
      <ProtoSwitcher {...{ role, screen, selected, switchRole, setScreen, setSelected, protoOpen, setProtoOpen }} />
    </div>
  );
}

/* Detail rendered inside the admin shell (no public navbar wrapper) */
function DetailInline({ t }) {
  return (
    <div style={{ maxWidth: 880 }}>
      <div className="card" style={{ overflow: "hidden" }}>
        <div style={{ height: 6, background: "var(--cyan)" }} />
        <div className="card-pad" style={{ padding: "28px 32px 32px" }}>
          <div style={{ display: "flex", gap: 10, alignItems: "center", marginBottom: 14, flexWrap: "wrap" }}>
            <Badge tone="cyan">{t.year}</Badge>
            <span style={{ fontSize: 13.5, fontWeight: 600, color: "var(--fg-2)" }}>{t.program}</span>
          </div>
          <h1 style={{ margin: "0 0 16px", fontSize: 27, fontWeight: 700, lineHeight: 1.2, color: "var(--navy)", textWrap: "pretty" }}>{t.title}</h1>
          <div style={{ marginBottom: 22 }}><ChipRow items={t.authors} kind="person" /></div>
          <div className="sec-label">Abstract</div>
          <p style={{ margin: "0 0 24px", fontSize: 15.5, lineHeight: 1.65 }}>{t.abstract}</p>
          <div className="sec-label">Recommendations</div>
          <p style={{ margin: "0 0 20px", fontSize: 15.5, lineHeight: 1.65 }}>{t.recommendations}</p>
          <div style={{ borderTop: "1px solid var(--line)", paddingTop: 16, display: "grid", gap: 14 }}>
            <div><div style={{ fontSize: 12, fontWeight: 700, textTransform: "uppercase", letterSpacing: ".05em", color: "var(--fg-3)", marginBottom: 8 }}>Adviser</div><ChipRow items={t.advisers} kind="person" /></div>
            <div><div style={{ fontSize: 12, fontWeight: 700, textTransform: "uppercase", letterSpacing: ".05em", color: "var(--fg-3)", marginBottom: 8 }}>Panelists</div><ChipRow items={t.panelists} kind="person" /></div>
            <div><div style={{ fontSize: 12, fontWeight: 700, textTransform: "uppercase", letterSpacing: ".05em", color: "var(--fg-3)", marginBottom: 8 }}>Keywords</div><ChipRow items={t.keywords} kind="key" /></div>
          </div>
        </div>
      </div>
    </div>
  );
}

/* ---------- Prototype role/screen switcher ---------- */
function ProtoSwitcher({ role, screen, selected, switchRole, setScreen, setSelected, protoOpen, setProtoOpen }) {
  const realRole = role === "auth" ? "viewer" : role;
  const links = {
    viewer: [
      { id: "browse", label: "Browse / Search", go: () => { switchRole("viewer"); } },
      { id: "detail", label: "Thesis detail", go: () => { switchRole("viewer"); setTimeout(() => setSelected(window.THESES[0]), 0); } },
      { id: "login", label: "Login screen", go: () => { setScreen("login"); } },
    ],
    department: [
      { id: "dashboard", label: "My theses (dashboard)", go: () => { switchRole("department"); } },
      { id: "form", label: "Add / Edit form", go: () => { switchRole("department"); setTimeout(() => setScreen("form"), 0); } },
    ],
    admin: [
      { id: "accounts", label: "Department accounts", go: () => { switchRole("admin"); } },
      { id: "activity", label: "Activity log", go: () => { switchRole("admin"); setTimeout(() => setScreen("activity"), 0); } },
    ],
  };
  const cur = selected && realRole === "viewer" ? "detail" : screen;

  return (
    <div className="proto">
      {protoOpen && (
        <div className="proto-panel">
          <h4>Prototype navigator</h4>
          <div className="proto-roles">
            {["viewer", "department", "admin"].map((r) => (
              <button key={r} className={`proto-role${realRole === r ? " active" : ""}`} onClick={() => switchRole(r)}>{r[0].toUpperCase() + r.slice(1)}</button>
            ))}
          </div>
          <div className="proto-links">
            {links[realRole].map((l) => (
              <button key={l.id} className={`proto-link${cur === l.id ? " active" : ""}`} onClick={l.go}>{l.label}</button>
            ))}
          </div>
        </div>
      )}
      <button className="proto-toggle" onClick={() => setProtoOpen((o) => !o)}>
        <span className="pdot" /> {protoOpen ? "Close" : "Screens"}
      </button>
    </div>
  );
}

Object.assign(window, { App, DetailInline, ProtoSwitcher });

ReactDOM.createRoot(document.getElementById("root")).render(<App />);
