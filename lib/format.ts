export const RUBRIKEN = [
  "Amtliches", "Vereine", "Veranstaltungen", "Wirtschaft", "Kirchen & Soziales", "Sonstiges",
] as const;

export const KATEGORIEN = {
  verein: "Verein",
  betrieb: "Betrieb / Wirtschaft",
  institution: "Institution / Organisation",
  gemeinde: "Gemeinde (Mitarbeiter:in)",
  privat: "Privatperson",
} as const;

export const STATUS_LABEL: Record<string, string> = {
  neu: "Neu",
  pruefung: "In Prüfung",
  rueckfrage: "Rückfrage offen",
  freigegeben: "Für Agentur freigegeben",
  pdf_offen: "PDF-Freigabe läuft",
  pdf_aenderung: "PDF: Änderungswunsch",
  pdf_ok: "PDF freigegeben",
};

export function fmtDate(iso: string | Date): string {
  return new Date(iso).toLocaleDateString("de-AT", {
    day: "2-digit", month: "2-digit", year: "numeric",
  });
}

export function fmtDateTime(iso: string | Date): string {
  const d = new Date(iso);
  return `${fmtDate(d)}, ${d.toLocaleTimeString("de-AT", { hour: "2-digit", minute: "2-digit" })} Uhr`;
}

export function absenderName(s: { organisation?: string | null; vorname: string; nachname: string }): string {
  return s.organisation || `${s.vorname} ${s.nachname}`.trim();
}

export function neueRef(art: string, jahr: number, laufnummer: number): string {
  const prefix = art === "partnerstory" ? "PR" : art.startsWith("inserat") ? "INS" : "GZ";
  return `${prefix}-${jahr}-${String(laufnummer).padStart(3, "0")}`;
}
