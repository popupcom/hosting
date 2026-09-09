import "server-only";
import { createHmac, timingSafeEqual } from "crypto";
import { env } from "@/lib/env";

// Signierte Freigabe-Links: Token = <approvalId>.<hmac>, kein Login nötig.

function hmac(id: string): string {
  return createHmac("sha256", env("APPROVAL_LINK_SECRET")).update(id).digest("hex");
}

export function signApprovalToken(approvalId: string): string {
  return `${approvalId}.${hmac(approvalId)}`;
}

export function verifyApprovalToken(token: string): string | null {
  const dot = token.lastIndexOf(".");
  if (dot <= 0) return null;
  const id = token.slice(0, dot);
  const sig = token.slice(dot + 1);
  const expected = hmac(id);
  if (sig.length !== expected.length) return null;
  try {
    if (!timingSafeEqual(Buffer.from(sig, "hex"), Buffer.from(expected, "hex"))) return null;
  } catch {
    return null;
  }
  return id;
}
