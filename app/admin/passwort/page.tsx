"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import { supabaseBrowser } from "@/lib/supabase/browser";

// Nach einer Einladung: eigenes Passwort setzen.
export default function PasswortPage() {
  const router = useRouter();
  const [pw, setPw] = useState("");
  const [msg, setMsg] = useState<string | null>(null);

  async function save(e: React.FormEvent) {
    e.preventDefault();
    if (pw.length < 10) { setMsg("Bitte mindestens 10 Zeichen."); return; }
    const { error } = await supabaseBrowser().auth.updateUser({ password: pw });
    if (error) { setMsg("Fehler: " + error.message); return; }
    router.push("/admin");
    router.refresh();
  }

  return (
    <div className="wrap" style={{ maxWidth: 480 }}>
      <h1 className="sechead">Passwort festlegen</h1>
      <div className="secline" />
      <form onSubmit={save} className="card" style={{ padding: "2rem", display: "flex", flexDirection: "column", gap: "1rem" }}>
        <div className="field">
          <label>Neues Passwort (mind. 10 Zeichen)</label>
          <input type="password" value={pw} onChange={(e) => setPw(e.target.value)} required />
        </div>
        {msg && <p className="err">{msg}</p>}
        <button className="btn btn-primary" type="submit">Speichern &amp; weiter</button>
      </form>
    </div>
  );
}
