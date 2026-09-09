import { z } from "zod";

export const photoMetaSchema = z.object({
  name: z.string().min(1).max(200),
  width: z.number().int().positive().max(30000),
  height: z.number().int().positive().max(30000),
  bytes: z.number().int().positive().max(25 * 1024 * 1024), // 25 MB
  contentType: z.string().regex(/^image\//),
});

export const submissionSchema = z.object({
  issueSlug: z.string().min(1),
  kategorie: z.enum(["verein", "betrieb", "institution", "gemeinde", "privat"]),
  organisation: z.string().max(200).optional().default(""),
  vorname: z.string().min(1).max(100),
  nachname: z.string().min(1).max(100),
  funktion: z.string().max(150).optional().default(""),
  email: z.string().email().max(200),
  telefon: z.string().max(50).optional().default(""),
  rubrik: z.string().max(50),
  titel: z.string().min(1).max(90),
  einleitung: z.string().max(300).optional().default(""),
  text: z.string().min(1).max(20000),
  credits: z.string().max(200).optional().default(""),
  rechteOk: z.literal(true),
  photos: z.array(photoMetaSchema).max(10).default([]),
}).refine((d) => d.kategorie === "privat" || d.organisation.trim().length > 0, {
  message: "Organisation ist für Nicht-Privatpersonen Pflicht",
  path: ["organisation"],
});

export type SubmissionInput = z.infer<typeof submissionSchema>;
