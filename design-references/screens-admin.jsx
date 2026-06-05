/* ============================================================
   Admin-shell screens: Department Dashboard, Department Accounts,
   Activity Log, and the Delete-account confirmation modal.
   ============================================================ */

/* ---------- 4. DEPARTMENT DASHBOARD ---------- */
function DashboardScreen({ deptName, theses, onAdd, onEdit, onDelete, onOpen }) {
  const [q, setQ] = React.useState("");
  const rows = theses.filter((t) => (t.title + " " + t.authors.join(" ")).toLowerCase().includes(q.toLowerCase()));

  return (
    <div className="content-wrap">
      <PageHead
        title="My theses"
        sub={`${deptName} — records owned by your department`}
        actions={<Btn variant="primary" icon="plus" onClick={onAdd}>Add Thesis</Btn>}
      />

      <div className="stat-row" style={{ marginBottom: 22 }}>
        <div className="stat"><div className="k">Total records</div><div className="v">{theses.length}</div></div>
        <div className="stat"><div className="k">Latest year</div><div className="v gold">{theses.length ? Math.max(...theses.map((t) => t.year)) : "—"}</div></div>
        <div className="stat"><div className="k">Last updated</div><div className="v green" style={{ fontSize: 19, paddingTop: 8 }}>{theses.length ? theses.map((t) => t.updated).sort().reverse()[0] : "—"}</div></div>
      </div>

      <div className="card">
        <div className="card-head">
          <h3>Thesis records</h3>
          <div className="searchbar" style={{ width: 280 }}>
            <span style={{ position: "absolute", left: 13, top: "50%", transform: "translateY(-50%)", color: "var(--fg-3)" }}><Icon name="search" size={16} /></span>
            <input className="input" style={{ padding: "8px 12px 8px 38px", fontSize: 14 }} placeholder="Search my theses…" value={q} onChange={(e) => setQ(e.target.value)} />
          </div>
        </div>
        {rows.length > 0 ? (
          <div className="tbl-wrap">
            <table className="tbl">
              <thead><tr>
                <th>Title</th><th style={{ width: 80 }}>Year</th><th>Authors</th><th style={{ width: 130 }}>Last updated</th><th style={{ width: 110, textAlign: "right" }}>Actions</th>
              </tr></thead>
              <tbody>
                {rows.map((t) => (
                  <tr key={t.id}>
                    <td><span className="t-title" onClick={() => onOpen(t)}>{t.title}</span></td>
                    <td className="num">{t.year}</td>
                    <td className="t-muted">{t.authors.join(", ")}</td>
                    <td className="t-muted tabnums">{t.updated}</td>
                    <td>
                      <div className="actions">
                        <button className="btn-icon" title="Edit" onClick={() => onEdit(t)}><Icon name="edit" size={16} /></button>
                        <button className="btn-icon danger" title="Delete" onClick={() => onDelete(t)}><Icon name="trash" size={16} /></button>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        ) : (
          <div className="empty">
            <div className="empty-mark"><Icon name="book" size={30} /></div>
            <h3>{q ? "No matching records" : "No theses yet"}</h3>
            <p>{q ? "Try a different search term." : "Add your department's first thesis record to start building the catalog."}</p>
            {!q && <div style={{ marginTop: 18 }}><Btn variant="primary" icon="plus" onClick={onAdd}>Add Thesis</Btn></div>}
          </div>
        )}
      </div>
    </div>
  );
}

/* ---------- 7. DEPARTMENT ACCOUNTS (admin) ---------- */
function AccountsScreen({ accounts, onCreate, onDeleteAccount, onToast }) {
  const [q, setQ] = React.useState("");
  const rows = accounts.filter((a) => (a.dept + " " + a.username).toLowerCase().includes(q.toLowerCase()));

  return (
    <div className="content-wrap">
      <PageHead
        title="Department accounts"
        sub="Create and manage the department logins that can catalog theses."
        actions={<Btn variant="primary" icon="plus" onClick={onCreate}>Create department account</Btn>}
      />

      <div className="card">
        <div className="card-head">
          <h3>{accounts.length} accounts</h3>
          <div className="searchbar" style={{ width: 280 }}>
            <span style={{ position: "absolute", left: 13, top: "50%", transform: "translateY(-50%)", color: "var(--fg-3)" }}><Icon name="search" size={16} /></span>
            <input className="input" style={{ padding: "8px 12px 8px 38px", fontSize: 14 }} placeholder="Search accounts…" value={q} onChange={(e) => setQ(e.target.value)} />
          </div>
        </div>
        <div className="tbl-wrap">
          <table className="tbl">
            <thead><tr>
              <th>Department</th><th>Username / email</th><th style={{ width: 120 }}>Status</th><th style={{ width: 90 }}>Records</th><th style={{ width: 130 }}>Created</th><th style={{ width: 180, textAlign: "right" }}>Actions</th>
            </tr></thead>
            <tbody>
              {rows.map((a) => (
                <tr key={a.id}>
                  <td style={{ fontWeight: 600 }}>{a.dept}</td>
                  <td className="t-muted">{a.username}</td>
                  <td>{a.status === "active"
                    ? <Badge tone="green" dot>Active</Badge>
                    : <Badge tone="gray" dot>Inactive</Badge>}</td>
                  <td className="num">{a.records}</td>
                  <td className="t-muted tabnums">{a.created}</td>
                  <td>
                    <div className="actions">
                      <button className="btn btn-secondary btn-sm" onClick={() => onToast("Edit account form would open.")}>Edit</button>
                      <button className="btn-icon" title={a.status === "active" ? "Deactivate" : "Activate"} onClick={() => onToast(a.status === "active" ? "Account deactivated." : "Account activated.")}>
                        <Icon name={a.status === "active" ? "lock" : "check"} size={15} />
                      </button>
                      <button className="btn-icon danger" title="Delete" onClick={() => onDeleteAccount(a)}><Icon name="trash" size={16} /></button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}

/* ---------- 9. ACTIVITY LOG (admin) ---------- */
function ActivityScreen({ log }) {
  const [q, setQ] = React.useState("");
  const [action, setAction] = React.useState("");
  const [date, setDate] = React.useState("");

  const actions = [...new Set(log.map((l) => l.action))];
  const rows = log.filter((l) => {
    const hay = (l.actor + " " + l.target + " " + l.action).toLowerCase();
    return (!q || hay.includes(q.toLowerCase())) && (!action || l.action === action) && (!date || l.time.startsWith(date));
  });

  const tone = { created: "green", edited: "cyan", deleted: "red", deactivated: "gold" };

  return (
    <div className="content-wrap">
      <PageHead title="Activity log" sub="Audit trail of every account and thesis action across the system." />

      <div className="card card-pad" style={{ padding: "16px 18px", marginBottom: 20, display: "flex", gap: 14, alignItems: "flex-end", flexWrap: "wrap" }}>
        <div className="searchbar" style={{ flex: 1, minWidth: 220 }}>
          <span style={{ position: "absolute", left: 13, top: "50%", transform: "translateY(-50%)", color: "var(--fg-3)" }}><Icon name="search" size={16} /></span>
          <input className="input" style={{ padding: "9px 12px 9px 38px" }} placeholder="Search actor or affected record…" value={q} onChange={(e) => setQ(e.target.value)} />
        </div>
        <Field label="Action type"><select className="select" style={{ minWidth: 150 }} value={action} onChange={(e) => setAction(e.target.value)}><option value="">All actions</option>{actions.map((a) => <option key={a} value={a} style={{ textTransform: "capitalize" }}>{a}</option>)}</select></Field>
        <Field label="Date"><select className="select" style={{ minWidth: 140 }} value={date} onChange={(e) => setDate(e.target.value)}><option value="">All dates</option><option value="2026-05">May 2026</option><option value="2026-04">April 2026</option></select></Field>
      </div>

      <div className="card">
        <div className="tbl-wrap">
          <table className="tbl">
            <thead><tr>
              <th style={{ width: 220 }}>Actor</th><th style={{ width: 140 }}>Action</th><th>Affected record</th><th style={{ width: 170 }}>Timestamp</th>
            </tr></thead>
            <tbody>
              {rows.length > 0 ? rows.map((l) => (
                <tr key={l.id}>
                  <td>
                    <div style={{ fontWeight: 600 }}>{l.actor}</div>
                    <div style={{ fontSize: 12, color: "var(--fg-3)", fontWeight: 600 }}>{l.actorRole}</div>
                  </td>
                  <td><Badge tone={tone[l.action] || "gray"}>{l.action}</Badge> <span style={{ fontSize: 12, color: "var(--fg-3)", marginLeft: 2 }}>{l.type}</span></td>
                  <td className="t-muted">{l.target}</td>
                  <td className="t-muted tabnums">{l.time}</td>
                </tr>
              )) : (
                <tr><td colSpan="4"><div className="empty" style={{ padding: "44px 24px" }}><div className="empty-mark"><Icon name="activity" size={28} /></div><h3>No matching activity</h3><p>Adjust the filters or search term to see logged actions.</p></div></td></tr>
              )}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}

/* ---------- 8. DELETE DEPARTMENT ACCOUNT CONFIRMATION ---------- */
function DeleteAccountModal({ account, onClose, onConfirm }) {
  return (
    <Modal title="Delete department account" icon="trash" onClose={onClose} width={520}
      footer={<>
        <Btn variant="secondary" onClick={onClose}>Cancel</Btn>
        <Btn variant="navy" onClick={() => onConfirm("keep")}>Keep the records</Btn>
        <Btn variant="danger" icon="trash" onClick={() => onConfirm("delete")}>Delete the records too</Btn>
      </>}>
      <p style={{ margin: "0 0 16px", fontSize: 15.5, lineHeight: 1.55 }}>
        You're about to delete the account for <strong>{account.dept}</strong> (<span style={{ color: "var(--fg-2)" }}>{account.username}</span>).
      </p>
      <div className="banner warn" style={{ marginBottom: 18 }}>
        <Icon name="book" size={18} />
        <div>This department owns <strong>{account.records} thesis {account.records === 1 ? "record" : "records"}</strong>. Choose what happens to them.</div>
      </div>
      <div className="col" style={{ gap: 12 }}>
        <div style={{ border: "1px solid var(--line)", borderRadius: "var(--r-md)", padding: "14px 16px" }}>
          <div style={{ fontWeight: 700, color: "var(--navy)", marginBottom: 3 }}>Keep the records</div>
          <div style={{ fontSize: 13.5, color: "var(--fg-2)", lineHeight: 1.5 }}>The {account.records} {account.records === 1 ? "record stays" : "records stay"} in the public archive but become unowned. They can be reassigned later.</div>
        </div>
        <div style={{ border: "1px solid #f1c9c3", borderRadius: "var(--r-md)", padding: "14px 16px", background: "var(--danger-soft)" }}>
          <div style={{ fontWeight: 700, color: "var(--danger-dark)", marginBottom: 3 }}>Delete the records too</div>
          <div style={{ fontSize: 13.5, color: "var(--danger-dark)", lineHeight: 1.5 }}>Permanently removes the account and all {account.records} thesis {account.records === 1 ? "record" : "records"} from the archive. This cannot be undone.</div>
        </div>
      </div>
    </Modal>
  );
}

Object.assign(window, { DashboardScreen, AccountsScreen, ActivityScreen, DeleteAccountModal });
