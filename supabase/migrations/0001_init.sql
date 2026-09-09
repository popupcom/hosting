-- Gemeindezeitung Schruns – Basisschema (Phase 1)

create extension if not exists "pgcrypto";

-- ---------- Rollen / Profile ----------
create type app_role as enum ('superadmin', 'redakteur');

create table public.profiles (
  user_id uuid primary key references auth.users (id) on delete cascade,
  name text not null default '',
  email text not null,
  role app_role not null default 'redakteur',
  aktiv boolean not null default true,
  created_at timestamptz not null default now()
);

-- Bei neuem Auth-User automatisch Profil anlegen
create or replace function public.handle_new_user()
returns trigger language plpgsql security definer set search_path = public as $$
begin
  insert into public.profiles (user_id, email, name)
  values (new.id, new.email, coalesce(new.raw_user_meta_data->>'name', ''))
  on conflict (user_id) do nothing;
  return new;
end $$;

create trigger on_auth_user_created
  after insert on auth.users
  for each row execute function public.handle_new_user();

-- ---------- Ausgaben ----------
create table public.issues (
  id uuid primary key default gen_random_uuid(),
  slug text not null unique,               -- z. B. 2026-11
  label text not null,                     -- z. B. November 2026
  redaktionsschluss timestamptz not null,
  druckfreigabe_am date,
  erscheint_am date,                       -- "wann geht das Projekt online"
  aktiv boolean not null default true,
  created_at timestamptz not null default now()
);

-- ---------- Einreichungen ----------
create type submission_art as enum ('beitrag', 'partnerstory', 'inserat1', 'inserat12');
create type submission_status as enum (
  'neu', 'pruefung', 'rueckfrage', 'freigegeben',
  'pdf_offen', 'pdf_aenderung', 'pdf_ok'
);

create table public.submissions (
  id uuid primary key default gen_random_uuid(),
  ref text not null unique,
  art submission_art not null default 'beitrag',
  issue_id uuid not null references public.issues (id),
  rubrik text,
  kategorie text,                          -- verein | betrieb | institution | gemeinde | privat
  organisation text,
  vorname text not null,
  nachname text not null,
  funktion text,
  email text not null,
  telefon text,
  titel text not null,
  einleitung text,
  text text,
  credits text,
  preis numeric(10,2),
  platzierung text,
  status submission_status not null default 'neu',
  assigned_to uuid references public.profiles (user_id),
  assigned_at timestamptz,
  korrekturschleifen int not null default 0,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create index on public.submissions (issue_id, status);
create index on public.submissions (assigned_to);

create table public.submission_photos (
  id uuid primary key default gen_random_uuid(),
  submission_id uuid not null references public.submissions (id) on delete cascade,
  storage_path text not null,
  original_name text not null,
  width int, height int, bytes bigint,
  uploaded boolean not null default false,
  created_at timestamptz not null default now()
);

-- Verlauf / Audit-Trail
create table public.submission_events (
  id uuid primary key default gen_random_uuid(),
  submission_id uuid not null references public.submissions (id) on delete cascade,
  typ text not null,                       -- system|status|rueckfrage|notiz|mail|freigabe|zuweisung
  akteur text not null,                    -- "System", Profilname oder "Einsender:in"
  akteur_id uuid,
  text text not null,
  created_at timestamptz not null default now()
);
create index on public.submission_events (submission_id, created_at);

-- ---------- Druckfreigaben ----------
create type approval_decision as enum ('offen', 'freigegeben', 'aenderung', 'auto');

create table public.approvals (
  id uuid primary key default gen_random_uuid(),
  submission_id uuid not null references public.submissions (id) on delete cascade,
  runde int not null default 1,
  pdf_storage_path text not null,
  pdf_name text not null,
  gesendet_an text not null,
  frist timestamptz not null,
  gesendet_am timestamptz not null default now(),
  entschieden_am timestamptz,
  entscheidung approval_decision not null default 'offen',
  aenderungswunsch text
);
create index on public.approvals (submission_id, runde);
create index on public.approvals (entscheidung, frist);

-- ---------- Storage-Buckets (privat) ----------
insert into storage.buckets (id, name, public) values ('photos', 'photos', false)
  on conflict (id) do nothing;
insert into storage.buckets (id, name, public) values ('approvals', 'approvals', false)
  on conflict (id) do nothing;

-- ---------- RLS: standardmäßig alles dicht ----------
-- Zugriff läuft in Phase 1 ausschließlich über Server-Code (Service Role);
-- Redakteurs-Rechte werden serverseitig über profiles.role geprüft.
alter table public.profiles enable row level security;
alter table public.issues enable row level security;
alter table public.submissions enable row level security;
alter table public.submission_photos enable row level security;
alter table public.submission_events enable row level security;
alter table public.approvals enable row level security;

-- Eingeloggte dürfen ihr eigenes Profil lesen (für Rollen-Anzeige im UI)
create policy "read own profile" on public.profiles
  for select using (auth.uid() = user_id);

-- Aktive Ausgaben sind öffentlich lesbar (Formular-Auswahl)
create policy "read active issues" on public.issues
  for select using (aktiv = true);

-- updated_at automatisch pflegen
create or replace function public.touch_updated_at()
returns trigger language plpgsql as $$
begin new.updated_at = now(); return new; end $$;
create trigger submissions_touch before update on public.submissions
  for each row execute function public.touch_updated_at();
