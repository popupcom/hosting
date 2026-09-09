"use client";

import { useMemo, useRef, useState } from "react";
import { supabaseBrowser } from "@/lib/supabase/browser";
import { KATEGORIEN, RUBRIKEN } from "@/lib/format";

type Issue = { slug: string; label: string; redaktionsschluss: string };
type Photo = { file: File; width: number; height: number; preview: string };

const RUBRIK_DEFAULT: Record<string, string> = {
  verein: "Vereine", betrieb: "Wirtschaft", institution: "Sonstiges",
  gemeinde: "Amtliches", privat: "Sonstiges",
};

export default function SubmissionWizard({ issues }: { issues: Issue[] }) {
  const [step, setStep] = useState(1);
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [ref, setRef] = useState<string | null>(null);
  const [photos, setPhotos] = useState<Photo[]>([]);
  const fileInput = useRef<HTMLInputElement>(null);

  const [f, setF] = useState({
    kategorie: "", organisation: "", vorname: "", nachname: "", funktion: "",
    email: "", telefon: "", issueSlug: issues[0]?.slug ?? "", rubrik: "Vereine",
    titel: "", einleitung: "", text: "", credits: "", rechteOk: false,
  });
  const set = (k: string, v: string | boolean) => setF((p) => ({ ...p, [k]: v }));

  const seiten = useMemo(() => {
    const z = f.text.length + f.einleitung.length;
    const s = z / 3500 + photos.length * 0.15;
    return Math.max(0, Math.round(s * 4) / 4);
  }, [f.text, f.einleitung, photos.length]);

  function validStep1(): string | null {
    if (!f.kategorie) return "Bitte wählen Sie eine Kategorie.";
    if (f.kategorie !== "privat" && !f.organisation.trim())
      return "Bitte den Namen von Verein/Betrieb/Organisation angeben.";
    if (!f.vorname.trim() || !f.nachname.trim()) return "Bitte Vor- und Nachnamen angeben.";
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(f.email)) return "Bitte eine gültige E-Mail-Adresse angeben.";
    return null;
  }

  async function addFiles(list: FileList | null) {
    if (!list) return;
    const next: Photo[] = [];
    for (const file of Array.from(list)) {
      if (!file.type.startsWith("image/")) continue;
      if (file.size > 25 * 1024 * 1024) { setError(`„${file.name}“ ist größer als 25 MB.`); continue; }
      const dims = await new Promise<{ w: number; h: number }>((resolve) => {
        const url = URL.createObjectURL(file);
        const img = new Image();
        img.onload = () => { resolve({ w: img.naturalWidth, h: img.naturalHeight }); URL.revokeObjectURL(url); };
        img.onerror = () => { resolve({ w: 0, h: 0 }); URL.revokeObjectURL(url); };
        img.src = url;
      });
      next.push({ file, width: dims.w, height: dims.h, preview: URL.createObjectURL(file) });
    }
    setPhotos((p) => [...p, ...next].slice(0, 10));
  }

  async function submit() {
    setError(null);
    if (!f.titel.trim() || !f.text.trim()) { setError("Bitte Titel und Text ausfüllen."); return; }
    if (!f.rechteOk) { setError("Bitte die Rechte-Bestätigung ankreuzen."); return; }
    setBusy(true);
    try {
      const res = await fetch("/api/submissions", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          ...f,
          photos: photos.map((p) => ({
            name: p.file.name, width: p.width, height: p.height,
            bytes: p.file.size, contentType: p.file.type,
          })),
        }),
      });
      const data = await res.json();
      if (!res.ok) throw new Error(data.error ?? "Einreichung fehlgeschlagen.");

      // Fotos in Originalgröße direkt in den Storage laden (signierte URLs)
      const sb = supabaseBrowser();
      for (let i = 0; i < data.uploads.length; i++) {
        const u = data.uploads[i];
        const { error: upErr } = await sb.storage
          .from("photos")
          .uploadToSignedUrl(u.path, u.token, photos[i].file);
        if (upErr) throw new Error(`Foto-Upload fehlgeschlagen: ${upErr.message}`);
      }
      if (data.uploads.length) {
        await fetch("/api/submissions/confirm", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ token: data.confirmToken }),
        });
      }
      setRef(data.ref);
      setStep(3);
    } catch (e) {
      setError(e instanceof Error ? e.message : "Unbekannter Fehler.");
    } finally {
      setBusy(false);
    }
  }

  const stepCls = (n: number) =>
    `step${step === n ? " active" : ""}${step > n ? " done" : ""}`;

  return (
    <div>
      <div className="stepper">
        <div className={stepCls(1)}><span className="num">1</span><span className="lbl">Absender:in &amp; Kontakt</span></div>
        <div className={stepCls(2)}><span className="num">2</span><span className="lbl">Beitrag &amp; Fotos</span></div>
        <div className={stepCls(3)}><span className="num">3</span><span className="lbl">Fertig</span></div>
      </div>

      {error && <p className="err" style={{ marginBottom: "1rem" }}>{error}</p>}

      {step === 1 && (
        <section className="card" style={{ padding: "2rem" }}>
          <h2 style={{ color: "var(--c-head)", fontSize: "1.5rem", fontWeight: 600 }}>Absender:in &amp; Kontakt</h2>
          <p style={{ color: "var(--c-muted)", marginBottom: "1.4rem" }}>Damit wir bei Rückfragen wissen, an wen wir uns wenden dürfen.</p>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div className="field">
              <label>Ich reiche ein als <span className="req">*</span></label>
              <select value={f.kategorie} onChange={(e) => { set("kategorie", e.target.value); set("rubrik", RUBRIK_DEFAULT[e.target.value] ?? "Sonstiges"); }}>
                <option value="">Bitte wählen …</option>
                {Object.entries(KATEGORIEN).map(([k, v]) => <option key={k} value={k}>{v}</option>)}
              </select>
            </div>
            <div className="field">
              <label>Verein / Betrieb / Organisation</label>
              <input value={f.organisation} onChange={(e) => set("organisation", e.target.value)} placeholder="z. B. Musikverein Schruns" />
              <span className="hint">Bei Einsendungen als Privatperson leer lassen.</span>
            </div>
            <div className="field"><label>Vorname <span className="req">*</span></label>
              <input value={f.vorname} onChange={(e) => set("vorname", e.target.value)} /></div>
            <div className="field"><label>Nachname <span className="req">*</span></label>
              <input value={f.nachname} onChange={(e) => set("nachname", e.target.value)} /></div>
            <div className="field"><label>Funktion</label>
              <input value={f.funktion} onChange={(e) => set("funktion", e.target.value)} placeholder="z. B. Obmann, Geschäftsführerin …" /></div>
            <div className="field"><label>Telefon</label>
              <input value={f.telefon} onChange={(e) => set("telefon", e.target.value)} placeholder="+43 …" /></div>
            <div className="field md:col-span-2"><label>E-Mail-Adresse <span className="req">*</span></label>
              <input type="email" value={f.email} onChange={(e) => set("email", e.target.value)} placeholder="name@verein.at" />
              <span className="hint">Hierhin senden wir Eingangsbestätigung, Rückfragen und die Druckfreigabe.</span></div>
          </div>
          <div style={{ display: "flex", justifyContent: "flex-end", marginTop: "1.6rem" }}>
            <button className="btn btn-primary" onClick={() => {
              const e = validStep1(); if (e) { setError(e); } else { setError(null); setStep(2); }
            }}>Weiter zu Beitrag &amp; Fotos&ensp;→</button>
          </div>
        </section>
      )}

      {step === 2 && (
        <section className="card" style={{ padding: "2rem" }}>
          <h2 style={{ color: "var(--c-head)", fontSize: "1.5rem", fontWeight: 600 }}>Ihr Beitrag</h2>
          <p style={{ color: "var(--c-muted)", marginBottom: "1.4rem" }}>Titel, Einleitung und Text – die Redaktion behält sich Kürzungen vor.</p>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div className="field">
              <label>Ausgabe <span className="req">*</span></label>
              <select value={f.issueSlug} onChange={(e) => set("issueSlug", e.target.value)}>
                {issues.map((i) => <option key={i.slug} value={i.slug}>{i.label}</option>)}
              </select>
            </div>
            <div className="field">
              <label>Rubrik</label>
              <select value={f.rubrik} onChange={(e) => set("rubrik", e.target.value)}>
                {RUBRIKEN.map((r) => <option key={r}>{r}</option>)}
              </select>
            </div>
            <div className="field md:col-span-2">
              <label>Titel / Überschrift <span className="req">*</span></label>
              <input maxLength={90} value={f.titel} onChange={(e) => set("titel", e.target.value)} />
              <span className="hint">{f.titel.length} / 90 Zeichen</span>
            </div>
            <div className="field md:col-span-2">
              <label>Einleitung / Kurzzusammenfassung</label>
              <textarea maxLength={300} rows={3} value={f.einleitung} onChange={(e) => set("einleitung", e.target.value)} />
              <span className="hint">{f.einleitung.length} / 300 Zeichen</span>
            </div>
            <div className="field md:col-span-2">
              <label>Text <span className="req">*</span></label>
              <textarea rows={10} value={f.text} onChange={(e) => set("text", e.target.value)} />
              <span className="hint">
                {f.text.length.toLocaleString("de-AT")} Zeichen · Richtwert: max. 2.500
                {(f.text || photos.length > 0) && <> · entspricht ca. {seiten.toLocaleString("de-AT")} Seiten im Heft</>}
              </span>
            </div>
            <div className="field md:col-span-2">
              <label>Hinweis zu Texter:in / Fotograf:in</label>
              <input value={f.credits} onChange={(e) => set("credits", e.target.value)} placeholder="z. B. Text: Maria Muster · Fotos: Hans Beispiel" />
            </div>
          </div>

          <div className="infobox" style={{ margin: "1.2rem 0" }}>
            <div><b style={{ color: "var(--c-blue-dark)" }}>Wichtig für die Druckqualität:</b>{" "}
            Bitte laden Sie Ihre Fotos <b>unbearbeitet in Originalgröße</b> hoch – keine
            verkleinerten Bilder aus WhatsApp oder E-Mail-Anhängen.</div>
          </div>

          <div className="field">
            <label>Fotos hochladen <span className="hint" style={{ display: "inline" }}>(max. 10 · je max. 25 MB)</span></label>
            <input ref={fileInput} type="file" accept="image/*" multiple
              onChange={(e) => { addFiles(e.target.files); if (fileInput.current) fileInput.current.value = ""; }} />
          </div>
          {photos.length > 0 && (
            <ul style={{ listStyle: "none", padding: 0, marginTop: ".8rem", display: "flex", flexDirection: "column", gap: ".5rem" }}>
              {photos.map((p, i) => (
                <li key={i} className="card" style={{ display: "flex", alignItems: "center", gap: ".8rem", padding: ".5rem .8rem" }}>
                  {/* eslint-disable-next-line @next/next/no-img-element */}
                  <img src={p.preview} alt="" style={{ width: 52, height: 52, objectFit: "cover" }} />
                  <span style={{ flex: 1, minWidth: 0 }}>
                    <b style={{ fontSize: ".93rem" }}>{p.file.name}</b><br />
                    <span className="hint">{p.width.toLocaleString("de-AT")} × {p.height.toLocaleString("de-AT")} px · {(p.file.size / 1048576).toLocaleString("de-AT", { maximumFractionDigits: 1 })} MB</span>
                  </span>
                  {p.width < 2000 && p.height < 2000
                    ? <span className="badge b-rueckfrage">⚠ evtl. zu klein für Druck</span>
                    : <span className="badge b-freigegeben">✓ druckfähig</span>}
                  <button className="btn btn-ghost btn-sm" onClick={() => setPhotos((ps) => ps.filter((_, j) => j !== i))}>✕</button>
                </li>
              ))}
            </ul>
          )}

          <label style={{ display: "flex", gap: ".6rem", alignItems: "flex-start", fontSize: ".92rem", marginTop: "1rem" }}>
            <input type="checkbox" checked={f.rechteOk} onChange={(e) => set("rechteOk", e.target.checked)} style={{ width: "1.05rem", height: "1.05rem", marginTop: ".28rem" }} />
            <span>Ich bestätige, dass ich bzw. die einreichende Organisation über die erforderlichen{" "}
            <b>Bild- und Textrechte</b> verfügt, die abgebildeten Personen mit einer Veröffentlichung
            einverstanden sind und der Beitrag veröffentlicht werden darf. <span className="req">*</span></span>
          </label>

          <div style={{ display: "flex", justifyContent: "space-between", marginTop: "1.6rem" }}>
            <button className="btn btn-ghost" onClick={() => setStep(1)}>←&ensp;Zurück</button>
            <button className="btn btn-primary" disabled={busy} onClick={submit}>
              {busy ? "Wird übermittelt …" : "Beitrag absenden ✓"}
            </button>
          </div>
        </section>
      )}

      {step === 3 && (
        <section className="card" style={{ padding: "2.6rem", textAlign: "center" }}>
          <h2 style={{ color: "var(--c-head)", fontSize: "1.6rem", fontWeight: 600 }}>Vielen Dank für Ihren Beitrag!</h2>
          <p style={{ margin: "1rem 0" }}>
            <span className="badge b-neu" style={{ fontSize: "1.05rem", padding: ".5rem 1.2rem" }}>Referenz: {ref}</span>
          </p>
          <p>Ihr Beitrag wurde an die Redaktion übermittelt. Sie erhalten in Kürze eine Bestätigung per E-Mail.</p>
        </section>
      )}
    </div>
  );
}
