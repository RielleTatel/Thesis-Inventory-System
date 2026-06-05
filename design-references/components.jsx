/* ============================================================
   Shared UI components for the Thesis Inventory prototype
   ============================================================ */

/* ---------- Icons (simple inline line SVGs) ---------- */
function Icon({ name, size = 18, stroke = 2 }) {
  const p = {
    width: size, height: size, viewBox: "0 0 24 24", fill: "none",
    stroke: "currentColor", strokeWidth: stroke, strokeLinecap: "round", strokeLinejoin: "round",
    className: "ico",
  };
  const paths = {
    search: <><circle cx="11" cy="11" r="7" /><path d="M20 20l-3.5-3.5" /></>,
    book: <><path d="M4 5a2 2 0 0 1 2-2h12v16H6a2 2 0 0 0-2 2z" /><path d="M18 17H6a2 2 0 0 0-2 2" /></>,
    grid: <><rect x="3" y="3" width="7" height="7" rx="1" /><rect x="14" y="3" width="7" height="7" rx="1" /><rect x="3" y="14" width="7" height="7" rx="1" /><rect x="14" y="14" width="7" height="7" rx="1" /></>,
    plus: <><path d="M12 5v14M5 12h14" /></>,
    edit: <><path d="M12 20h9" /><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4z" /></>,
    trash: <><path d="M3 6h18" /><path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2" /><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" /></>,
    users: <><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M22 21v-2a4 4 0 0 0-3-3.87" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /></>,
    activity: <><path d="M22 12h-4l-3 9L9 3l-3 9H2" /></>,
    camera: <><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z" /><circle cx="12" cy="13" r="4" /></>,
    back: <><path d="M19 12H5" /><path d="M12 19l-7-7 7-7" /></>,
    logout: <><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" /><path d="M16 17l5-5-5-5" /><path d="M21 12H9" /></>,
    user: <><circle cx="12" cy="8" r="4" /><path d="M4 21v-1a6 6 0 0 1 6-6h4a6 6 0 0 1 6 6v1" /></>,
    lock: <><rect x="4" y="11" width="16" height="9" rx="2" /><path d="M8 11V8a4 4 0 0 1 8 0v3" /></>,
    google: null,
    menu: <><path d="M3 6h18M3 12h18M3 18h18" /></>,
    grip: <><circle cx="9" cy="6" r="1.4" /><circle cx="15" cy="6" r="1.4" /><circle cx="9" cy="12" r="1.4" /><circle cx="15" cy="12" r="1.4" /><circle cx="9" cy="18" r="1.4" /><circle cx="15" cy="18" r="1.4" /></>,
    x: <><path d="M18 6L6 18M6 6l12 12" /></>,
    upload: <><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" /><path d="M17 8l-5-5-5 5" /><path d="M12 3v12" /></>,
    check: <><path d="M20 6L9 17l-5-5" /></>,
    cal: <><rect x="3" y="4" width="18" height="18" rx="2" /><path d="M16 2v4M8 2v4M3 10h18" /></>,
    tag: <><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z" /><circle cx="7" cy="7" r="1.2" /></>,
    filter: <><path d="M22 3H2l8 9.46V19l4 2v-8.54z" /></>,
    chart: <><path d="M3 3v18h18" /><rect x="7" y="11" width="3" height="6" /><rect x="12" y="7" width="3" height="10" /><rect x="17" y="13" width="3" height="4" /></>,
  };
  return <svg {...p}>{paths[name] || null}</svg>;
}

function GoogleG({ size = 18 }) {
  return (
    <svg width={size} height={size} viewBox="0 0 48 48" aria-hidden="true">
      <path fill="#4285F4" d="M45 24c0-1.5-.1-3-.4-4.4H24v8.4h11.8c-.5 2.8-2 5.1-4.4 6.7v5.5h7.1C42.7 36.3 45 30.7 45 24z" />
      <path fill="#34A853" d="M24 46c5.9 0 10.9-2 14.5-5.3l-7.1-5.5c-2 1.3-4.5 2.1-7.4 2.1-5.7 0-10.5-3.8-12.2-9H4.5v5.7C8.1 41.1 15.4 46 24 46z" />
      <path fill="#FBBC05" d="M11.8 28.3c-.5-1.3-.7-2.8-.7-4.3s.3-3 .7-4.3v-5.7H4.5C3 17 2 20.4 2 24s1 7 2.5 10z" />
      <path fill="#EA4335" d="M24 11.4c3.2 0 6.1 1.1 8.4 3.3l6.3-6.3C34.9 4.9 29.9 3 24 3 15.4 3 8.1 7.9 4.5 14l7.3 5.7c1.7-5.2 6.5-9 12.2-9z" />
    </svg>
  );
}

/* ---------- Atoms ---------- */
function Btn({ variant = "secondary", size, icon, iconRight, children, ...rest }) {
  const cls = `btn btn-${variant}${size === "sm" ? " btn-sm" : ""}${rest.block ? " btn-block" : ""}`;
  const { block, ...dom } = rest;
  return (
    <button className={cls} {...dom}>
      {icon && <Icon name={icon} size={size === "sm" ? 15 : 17} />}
      {children}
      {iconRight && <Icon name={iconRight} size={size === "sm" ? 15 : 17} />}
    </button>
  );
}

function Chip({ kind = "person", children }) {
  return <span className={`chip chip-${kind}`}>{children}</span>;
}

function ChipRow({ items, kind }) {
  return (
    <div className="chip-row">
      {items.map((it, i) => <Chip key={i} kind={kind}>{it}</Chip>)}
    </div>
  );
}

function Badge({ tone = "gray", dot, children }) {
  return (
    <span className={`badge badge-${tone}`}>
      {dot && <span className="dot" />}
      {children}
    </span>
  );
}

function Field({ label, required, hint, error, children }) {
  return (
    <label className="field">
      {label && (
        <span className="field-label">{label}{required && <span className="req">*</span>}</span>
      )}
      {children}
      {hint && !error && <span className="field-hint">{hint}</span>}
      {error && <span className="field-error">{error}</span>}
    </label>
  );
}

/* ---------- Navbar ---------- */
function Brand({ onClick, light }) {
  return (
    <div className="navbar-brand" onClick={onClick}>
      <span className="brand-mark">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#02327C" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
          <path d="M4 5a2 2 0 0 1 2-2h12v16H6a2 2 0 0 0-2 2z" />
          <path d="M9 7h6M9 10h6" />
        </svg>
      </span>
      <div>
        <div className="brand-word">AdZU <span className="thin">Thesis Archives</span></div>
      </div>
    </div>
  );
}

function Navbar({ role, onBrand, rightLinks, user, onMenu }) {
  return (
    <header className="navbar">
      {onMenu && <button className="menu-btn" onClick={onMenu} aria-label="Menu"><Icon name="menu" /></button>}
      <Brand onClick={onBrand} />
      <div className="navbar-spacer" />
      {rightLinks}
      {user && (
        <div className="navbar-user">
          <div className="avatar">{user.initials}</div>
          <div className="navbar-user-meta">
            <div className="navbar-user-name">{user.name}</div>
            <div className="navbar-user-role">{user.role}</div>
          </div>
        </div>
      )}
    </header>
  );
}

/* ---------- Sidebar ---------- */
function Sidebar({ items, current, onNav, open, footer }) {
  return (
    <nav className={`sidebar${open ? " open" : ""}`}>
      {items.map((it, i) =>
        it.section ? (
          <div key={i} className="sidebar-section">{it.section}</div>
        ) : (
          <button
            key={i}
            className={`nav-item${current === it.id ? " active" : ""}`}
            onClick={() => onNav(it.id)}
          >
            <Icon name={it.icon} size={19} />
            {it.label}
          </button>
        )
      )}
      <div className="sidebar-foot">{footer}</div>
    </nav>
  );
}

/* ---------- Modal ---------- */
function Modal({ title, icon, onClose, children, footer, width = 560 }) {
  React.useEffect(() => {
    const h = (e) => { if (e.key === "Escape") onClose(); };
    window.addEventListener("keydown", h);
    return () => window.removeEventListener("keydown", h);
  }, []);
  return (
    <div className="overlay" onClick={onClose}>
      <div className="modal" style={{ maxWidth: width }} onClick={(e) => e.stopPropagation()}>
        <div className="modal-head">
          {icon && <Icon name={icon} size={20} />}
          <h3>{title}</h3>
          <button className="modal-x" onClick={onClose} aria-label="Close">&times;</button>
        </div>
        <div className="modal-body">{children}</div>
        {footer && <div className="modal-foot">{footer}</div>}
      </div>
    </div>
  );
}

/* ---------- Toast ---------- */
function Toast({ message }) {
  if (!message) return null;
  return (
    <div className="toast"><span className="ok"><Icon name="check" size={16} /></span>{message}</div>
  );
}

/* ---------- Page header ---------- */
function PageHead({ title, sub, actions }) {
  return (
    <div className="page-head">
      <div>
        <h1 className="page-title">{title}</h1>
        {sub && <p className="page-sub">{sub}</p>}
      </div>
      {actions && <div className="row gap-sm" style={{ gap: 10 }}>{actions}</div>}
    </div>
  );
}

Object.assign(window, {
  Icon, GoogleG, Btn, Chip, ChipRow, Badge, Field,
  Brand, Navbar, Sidebar, Modal, Toast, PageHead,
});
