import SubmissionWizard from "@/components/wizard/SubmissionWizard";
import { supabaseAdmin } from "@/lib/supabase/admin";
import { fmtDate } from "@/lib/format";

export const dynamic = "force-dynamic";

export default async function Home() {
  let issues: { slug: string; label: string; redaktionsschluss: string }[] = [];
  try {
    const { data } = await supabaseAdmin()
      .from("issues")
      .select("slug,label,redaktionsschluss")
      .eq("aktiv", true)
      .gte("redaktionsschluss", new Date().toISOString())
      .order("redaktionsschluss");
    issues = data ?? [];
  } catch {
    // Ohne konfigurierte DB (lokal) bleibt die Liste leer.
  }

  return (
    <div className="wrap">
      <h1 className="sechead">Gemeindezeitung – Beiträge einreichen</h1>
      <div className="secline" />
      <p style={{ maxWidth: "46rem", margin: "0 auto", textAlign: "center" }}>
        Ob Verein, Betrieb, Wirtschaftsgemeinschaft, Institution,
        Gemeindemitarbeiter:in oder Privatperson – hier können Sie Ihre Texte
        und Fotos für die Schrunser Gemeindezeitung bequem online einreichen.
      </p>
      {issues[0] && (
        <p style={{ textAlign: "center", marginTop: "1rem" }}>
          Einsendeschluss: <b style={{ color: "var(--c-blue)" }}>{fmtDate(issues[0].redaktionsschluss)}</b>
        </p>
      )}
      <SubmissionWizard issues={issues} />
    </div>
  );
}
