import type { Metadata } from "next";
import { Open_Sans } from "next/font/google";
import "./globals.css";
import Wappen from "@/components/Wappen";

const openSans = Open_Sans({ subsets: ["latin"], variable: "--font-open-sans" });

export const metadata: Metadata = {
  title: "Gemeindezeitung Schruns – Beiträge einreichen",
  description:
    "Beiträge und Fotos für die Gemeindezeitung der Marktgemeinde Schruns online einreichen.",
};

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="de-AT">
      <body className={openSans.variable}>
        <header>
          <div className="topbar">
            <div className="topbar-inner">
              <span className="mg">Marktgemeinde</span>
            </div>
          </div>
          <div className="namebar">
            <div className="namebar-inner">
              <span className="ort">Schruns</span>
              <span className="pagelabel">Gemeindezeitung</span>
              <Wappen className="wappenbox" />
            </div>
          </div>
        </header>
        <main>{children}</main>
        <footer className="site-foot">
          <div className="wrap" style={{ padding: "2rem 1.25rem" }}>
            <b style={{ color: "#fff" }}>Marktgemeinde Schruns</b>
            <br />
            Kirchplatz 2, 6780 Schruns ·{" "}
            <a href="mailto:gemeinde@schruns.at">gemeinde@schruns.at</a>
          </div>
          <div className="legal">
            © {new Date().getFullYear()} Marktgemeinde Schruns
          </div>
        </footer>
      </body>
    </html>
  );
}
