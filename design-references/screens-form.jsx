/* ============================================================
   5. Add/Edit thesis form  ·  6. OCR capture & review modal
   + Create department account modal + Delete thesis confirm
   ============================================================ */

/* ---------- Repeatable list editor ---------- */
function RepeatableList({ label, hint, values, onChange, placeholder, numbered = true }) {
  const set = (i, v) => { const next = [...values]; next[i] = v; onChange(next); };
  const add = () => onChange([...values, ""]);
  const remove = (i) => onChange(values.filter((_, j) => j !== i));
  return (
    <div className="field">
      <span className="field-label">{label}</span>
      {hint && <span className="field-hint" style={{ marginTop: -2, marginBottom: 2 }}>{hint}</span>}
      <div className="col" style={{ gap: 8 }}>
        {values.map((v, i) => (
          <div className="rep-row" key={i}>
            <span className="rep-handle" title="Drag to reorder"><Icon name="grip" size={16} /></span>
            {numbered && <span className="rep-num">{i + 1}</span>}
            <input className="input" value={v} placeholder={placeholder} onChange={(e) => set(i, e.target.value)} />
            <button className="btn-icon danger" title="Remove" onClick={() => remove(i)} disabled={values.length === 1} style={values.length === 1 ? { opacity: .4 } : null}><Icon name="x" size={16} /></button>
          </div>
        ))}
      </div>
      <button className="btn btn-secondary btn-sm" style={{ alignSelf: "flex-start", marginTop: 4 }} onClick={add}><Icon name="plus" size={15} /> Add another</button>
    </div>
  );
}

/* ---------- 5. ADD / EDIT FORM ---------- */
function FormScreen({ initial, scanApply, onCancel, onSave, onScan }) {
  const blank = { title: "", authors: [""], year: "", program: "", abstract: "", recommendations: "", advisers: [""], panelists: [""], keywords: [""] };
  const [f, setF] = React.useState(() => initial ? {
    ...initial,
    authors: initial.authors.length ? initial.authors : [""],
    advisers: initial.advisers.length ? initial.advisers : [""],
    panelists: initial.panelists.length ? initial.panelists : [""],
    keywords: initial.keywords.length ? initial.keywords : [""],
    year: String(initial.year),
  } : blank);
  const [errors, setErrors] = React.useState({});
  const upd = (k, v) => { setF((p) => ({ ...p, [k]: v })); setErrors((e) => ({ ...e, [k]: null })); };

  // Merge OCR-scanned text into the controlled field when it arrives.
  React.useEffect(() => {
    if (scanApply && scanApply.field) {
      setF((p) => ({ ...p, [scanApply.field]: scanApply.text }));
      setErrors((e) => ({ ...e, [scanApply.field]: null }));
    }
  }, [scanApply]);

  const MIN_YEAR = 1900;
  const MAX_YEAR = new Date().getFullYear();

  const save = () => {
    const e = {};
    if (!f.title.trim()) e.title = "Title is required.";
    const yr = +f.year;
    if (!f.year || isNaN(yr)) e.year = "Enter a valid year.";
    else if (!Number.isInteger(yr) || yr < MIN_YEAR || yr > MAX_YEAR) e.year = `Year must be between ${MIN_YEAR} and ${MAX_YEAR}.`;
    if (!f.program) e.program = "Select a program.";
    if (!f.abstract.trim()) e.abstract = "An abstract is required.";
    if (Object.keys(e).length) { setErrors(e); return; }
    onSave({
      ...f, year: +f.year,
      authors: f.authors.filter((x) => x.trim()),
      advisers: f.advisers.filter((x) => x.trim()),
      panelists: f.panelists.filter((x) => x.trim()),
      keywords: f.keywords.filter((x) => x.trim()),
    });
  };

  const scanBtn = (field) => (
    <button className="btn btn-secondary btn-sm" style={{ position: "absolute", top: 0, right: 0 }} onClick={() => onScan(field)}>
      <Icon name="camera" size={15} /> Scan from printed copy
    </button>
  );

  return (
    <div className="content-wrap" style={{ maxWidth: 900 }}>
      <PageHead title={initial ? "Edit thesis" : "Add thesis"} sub={initial ? "Update this record's descriptive information." : "Catalog a new thesis record. Fields marked * are required."} />

      <div className="card card-pad" style={{ padding: "26px 28px" }}>
        <div className="col" style={{ gap: 22 }}>
          {/* Core */}
          <Field label="Title" required error={errors.title}>
            <textarea className={`textarea${errors.title ? " invalid" : ""}`} style={{ minHeight: 64 }} value={f.title} onChange={(e) => upd("title", e.target.value)} placeholder="Full thesis title" />
          </Field>

          <div className="grid-2">
            <Field label="Year" required error={errors.year} hint={errors.year ? null : `Between ${MIN_YEAR} and ${MAX_YEAR}.`}>
              <input type="number" min={MIN_YEAR} max={MAX_YEAR} step="1" className={`input${errors.year ? " invalid" : ""}`} value={f.year} onChange={(e) => upd("year", e.target.value)} placeholder="e.g. 2025" inputMode="numeric" />
            </Field>
            <Field label="Program / Department" required error={errors.program}>
              <select className={`select${errors.program ? " invalid" : ""}`} value={f.program} onChange={(e) => upd("program", e.target.value)}>
                <option value="">Select a program…</option>
                {PROGRAMS.map((p) => <option key={p} value={p}>{p}</option>)}
              </select>
            </Field>
          </div>

          <RepeatableList label="Authors" hint="Listed in order of authorship." values={f.authors} placeholder="Author full name" onChange={(v) => upd("authors", v)} />

          {/* Abstract with scan */}
          <div style={{ position: "relative" }}>
            <Field label="Abstract" required error={errors.abstract} hint="A short description of the study.">
              <textarea className={`textarea${errors.abstract ? " invalid" : ""}`} style={{ minHeight: 130 }} value={f.abstract} onChange={(e) => upd("abstract", e.target.value)} placeholder="Summarize the study's aims, method, and key findings…" />
            </Field>
            {scanBtn("abstract")}
          </div>

          {/* Recommendations with scan */}
          <div style={{ position: "relative" }}>
            <Field label="Recommendations" hint="Suggested next steps or future work.">
              <textarea className="textarea" style={{ minHeight: 110 }} value={f.recommendations} onChange={(e) => upd("recommendations", e.target.value)} placeholder="Recommendations arising from the study…" />
            </Field>
            {scanBtn("recommendations")}
          </div>

          <div style={{ height: 1, background: "var(--line)" }} />

          <RepeatableList label="Adviser" values={f.advisers} placeholder="Adviser full name" onChange={(v) => upd("advisers", v)} />
          <RepeatableList label="Panelists" values={f.panelists} placeholder="Panelist full name" onChange={(v) => upd("panelists", v)} />
          <RepeatableList label="Keywords" hint="Used for search and filtering." values={f.keywords} placeholder="Keyword or topic" onChange={(v) => upd("keywords", v)} numbered={false} />
        </div>
      </div>

      <div style={{ display: "flex", gap: 12, justifyContent: "flex-end", marginTop: 20 }}>
        <Btn variant="secondary" onClick={onCancel}>Cancel</Btn>
        <Btn variant="primary" icon="check" onClick={save}>{initial ? "Save changes" : "Save thesis"}</Btn>
      </div>
    </div>
  );
}

/* ---------- 6. OCR CAPTURE & REVIEW MODAL ---------- */
function OcrModal({ field, onClose, onUse }) {
  const [stage, setStage] = React.useState("capture"); // capture -> review
  const [text, setText] = React.useState("");
  const fileRef = React.useRef(null);

  const runOcr = () => {
    setStage("processing");
    setTimeout(() => { setText(OCR_SAMPLE); setStage("review"); }, 1200);
  };

  const fieldName = field === "abstract" ? "Abstract" : "Recommendations";

  return (
    <Modal title={`Scan to ${fieldName}`} icon="camera" onClose={onClose} width={620}
      footer={stage === "review" ? <>
        <Btn variant="secondary" icon="back" onClick={() => setStage("capture")}>Retake</Btn>
        <Btn variant="primary" icon="check" onClick={() => onUse(field, text)}>Use this text</Btn>
      </> : <Btn variant="secondary" onClick={onClose}>Cancel</Btn>}>

      {stage === "capture" && (
        <div>
          <div className="banner" style={{ marginBottom: 18 }}>
            <Icon name="camera" size={18} />
            <div>Take a photo of the printed page or upload a scan. We'll extract the text so you can review it before it fills the {fieldName} field.</div>
          </div>
          <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 14 }}>
            <button className="card" style={{ padding: "30px 18px", cursor: "pointer", textAlign: "center", border: "2px dashed var(--line-strong)", background: "var(--surface-alt)" }} onClick={runOcr}>
              <div style={{ color: "var(--navy)", marginBottom: 10 }}><Icon name="camera" size={34} /></div>
              <div style={{ fontWeight: 700, fontSize: 15 }}>Take a photo</div>
              <div style={{ fontSize: 13, color: "var(--fg-2)", marginTop: 4 }}>Use your device camera</div>
            </button>
            <button className="card" style={{ padding: "30px 18px", cursor: "pointer", textAlign: "center", border: "2px dashed var(--line-strong)", background: "var(--surface-alt)" }} onClick={runOcr}>
              <div style={{ color: "var(--navy)", marginBottom: 10 }}><Icon name="upload" size={34} /></div>
              <div style={{ fontWeight: 700, fontSize: 15 }}>Upload an image</div>
              <div style={{ fontSize: 13, color: "var(--fg-2)", marginTop: 4 }}>JPG or PNG of the page</div>
            </button>
          </div>
        </div>
      )}

      {stage === "processing" && (
        <div style={{ padding: "44px 0", textAlign: "center" }}>
          <div className="ocr-spin" style={{ width: 46, height: 46, margin: "0 auto 18px", border: "4px solid var(--input-tint)", borderTopColor: "var(--navy)", borderRadius: "50%" }} />
          <div style={{ fontWeight: 600, fontSize: 16 }}>Extracting text…</div>
          <div style={{ color: "var(--fg-2)", fontSize: 14, marginTop: 4 }}>Reading the printed copy.</div>
        </div>
      )}

      {stage === "review" && (
        <div>
          <div style={{ display: "flex", gap: 16, alignItems: "stretch", marginBottom: 16 }}>
            <div style={{ width: 150, flexShrink: 0, borderRadius: "var(--r-md)", border: "1px solid var(--line)", background: "var(--input-tint)", display: "grid", placeItems: "center", color: "var(--fg-3)", minHeight: 120 }}>
              <div style={{ textAlign: "center" }}><Icon name="book" size={30} /><div style={{ fontSize: 12, marginTop: 6, fontWeight: 600 }}>Captured page</div></div>
            </div>
            <div style={{ flex: 1 }}>
              <div className="sec-label" style={{ marginBottom: 8 }}>Extracted text — review &amp; correct</div>
              <p style={{ margin: 0, fontSize: 13.5, color: "var(--fg-2)", lineHeight: 1.5 }}>Edit below to fix any OCR errors before adding it to the <strong>{fieldName}</strong> field. Nothing is saved until you choose <strong>Use this text</strong>.</p>
            </div>
          </div>
          <textarea className="textarea" style={{ minHeight: 200, lineHeight: 1.6 }} value={text} onChange={(e) => setText(e.target.value)} />
        </div>
      )}
    </Modal>
  );
}

/* ---------- Create department account modal ---------- */
function CreateAccountModal({ onClose, onCreate }) {
  const [dept, setDept] = React.useState("");
  const [email, setEmail] = React.useState("");
  return (
    <Modal title="Create department account" icon="users" onClose={onClose} width={500}
      footer={<>
        <Btn variant="secondary" onClick={onClose}>Cancel</Btn>
        <Btn variant="primary" icon="check" disabled={!dept || !email} onClick={() => onCreate({ dept, email })}>Create account</Btn>
      </>}>
      <div className="col" style={{ gap: 18 }}>
        <Field label="Department name" required><input className="input" value={dept} onChange={(e) => setDept(e.target.value)} placeholder="e.g. College of Architecture" /></Field>
        <Field label="Username / email" required><input className="input" value={email} onChange={(e) => setEmail(e.target.value)} placeholder="department@univ.edu" /></Field>
        <div className="banner"><Icon name="lock" size={18} /><div>A temporary password will be emailed to this address. The department sets a new one on first login.</div></div>
      </div>
    </Modal>
  );
}

/* ---------- Delete thesis confirm ---------- */
function DeleteThesisModal({ thesis, onClose, onConfirm }) {
  return (
    <Modal title="Delete thesis record" icon="trash" onClose={onClose} width={460}
      footer={<>
        <Btn variant="secondary" onClick={onClose}>Cancel</Btn>
        <Btn variant="danger" icon="trash" onClick={onConfirm}>Delete record</Btn>
      </>}>
      <p style={{ margin: 0, fontSize: 15.5, lineHeight: 1.55 }}>Permanently delete <strong>“{thesis.title}”</strong>? This removes it from the public archive and cannot be undone.</p>
    </Modal>
  );
}

Object.assign(window, { RepeatableList, FormScreen, OcrModal, CreateAccountModal, DeleteThesisModal });
