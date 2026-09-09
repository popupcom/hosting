import type { NextConfig } from "next";
import { withSentryConfig } from "@sentry/nextjs";

const nextConfig: NextConfig = {
  experimental: {
    serverActions: {
      // Druck-PDFs werden über Server Actions hochgeladen
      bodySizeLimit: "25mb",
    },
  },
};

// Sentry-Source-Maps nur hochladen, wenn ein Auth-Token vorhanden ist
export default process.env.SENTRY_AUTH_TOKEN
  ? withSentryConfig(nextConfig, {
      org: process.env.SENTRY_ORG,
      project: process.env.SENTRY_PROJECT,
      silent: true,
      widenClientFileUpload: true,
    })
  : nextConfig;
