"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import { supabaseBrowser } from "@/lib/supabase/browser";

export default function LoginPage() {
  const router = useRouter();
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [msg, setMsg] = useState<string | null>(null);
  const [busy, setBusy] = useState(false);

  async function loginPassword(e: React.FormEvent) {
    e.preventDefault();
    setBusy(true); setMsg(null);
    const { error } = await supabaseBrowser().auth.signInWithPassword({ email, password });
    setBusy(false);
    if (error) { setMsg("Anmeldung fehlgeschlagen: " + error.message); return; }
    router.push("/admin");
    router.refresh();
  }

  async function magicLink() {
    if (!email) { setMsg("Bitte zuerst die E-Mail-Adresse eingeben."); return; }
    setBusy(true); setMsg(null);
    const { error } = await supabaseBrowser().auth.signInWithOtp({
      email,
      options: { emailRedirectTo: `${window.location.origin}/auth/callback?next=/admin` },
    });
    setBusy(false);
    setMsg(error ? "Fehler: " + error.message : "Anmeldelink wurde per E-Mail gesendet.");
  }

  return (
    <div className="wrap" style={{ maxWidth: 480 }}>
      <h1 className="sechead">Redaktion – Anmeldung</h1>
      <div className="secline" />
      <form onSubmit={loginPassword} className="card" style={{ padding: "2rem", display: "flex", flexDirection: "column", gap: "1rem" }}>
        <div className="field">
          <label>E-Mail-Adresse</label>
          <input type="email" value={email} onChange={(e) => setEmail(e.target.value)} required />
        </div>
        <div className="field">
          <label>Passwort</label>
          <input type="password" value={password} onChange={(e) => setPassword(e.target.value)} />
        </div>
        {msg && <p className="err">{msg}</p>}
        <button className="btn btn-primary" disabled={busy} type="submit">Anmelden</button>
        <button className="btn btn-ghost" disabled={busy} type="button" onClick={magicLink}>
          Anmeldelink per E-Mail senden
        </button>
      </form>
    </div>
  );
}
