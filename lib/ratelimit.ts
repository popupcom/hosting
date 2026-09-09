import "server-only";
import { Ratelimit } from "@upstash/ratelimit";
import { Redis } from "@upstash/redis";
import { envOpt } from "@/lib/env";

// Upstash-Rate-Limiting für öffentliche Endpunkte. Ohne konfiguriertes
// Upstash (lokale Entwicklung) wird durchgelassen und nur gewarnt.
let limiter: Ratelimit | null | undefined;

function getLimiter(): Ratelimit | null {
  if (limiter !== undefined) return limiter;
  const url = envOpt("UPSTASH_REDIS_REST_URL");
  const token = envOpt("UPSTASH_REDIS_REST_TOKEN");
  if (!url || !token) {
    console.warn("[ratelimit] Upstash nicht konfiguriert – Rate Limiting inaktiv");
    limiter = null;
    return limiter;
  }
  limiter = new Ratelimit({
    redis: new Redis({ url, token }),
    limiter: Ratelimit.slidingWindow(10, "10 m"), // 10 Requests / 10 Minuten je IP+Aktion
    prefix: "gz",
  });
  return limiter;
}

export async function checkRateLimit(action: string, ip: string): Promise<boolean> {
  const l = getLimiter();
  if (!l) return true;
  const { success } = await l.limit(`${action}:${ip}`);
  return success;
}

export function clientIp(req: Request): string {
  return (
    req.headers.get("x-forwarded-for")?.split(",")[0]?.trim() ||
    req.headers.get("x-real-ip") ||
    "unknown"
  );
}
