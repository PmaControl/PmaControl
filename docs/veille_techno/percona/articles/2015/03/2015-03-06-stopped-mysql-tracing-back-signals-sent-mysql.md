---
title: What stopped MySQL? Tracing back signals sent to MySQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/stopped-mysql-tracing-back-signals-sent-mysql/
  post_id: 9091
source_author:
  name: Robert Barabas
  slug: robert-barabas
  url: https://www.percona.com/blog/author/robert-barabas/
  website: ''
published_at: '2015-03-06T16:59:24'
published_at_gmt: '2015-03-06T16:59:24'
modified_at: '2026-04-28T22:17:38'
modified_at_gmt: '2026-04-28T22:17:38'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- MySQL
category_slugs:
- mysql
tags:
- Auditd
- David Busby
- DevOps
- HUP
- KILL
- MySQL
- Perf
- Primary
- Robert Barabas
- SIGHUP
- SIGKILL
- signal
- SIGTERM
- systemtap
- TERM
tag_slugs:
- auditd
- david-busby
- devops
- hup
- kill
- mysql
- perf
- primary
- robert-barabas
- sighup
- sigkill
- signal
- sigterm
- systemtap
- term
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# What stopped MySQL? Tracing back signals sent to MySQL

Source: [Percona Blog](https://www.percona.com/blog/stopped-mysql-tracing-back-signals-sent-mysql/)

Auteur source: [Robert Barabas](https://www.percona.com/blog/author/robert-barabas/)

Publication: 2015-03-06T16:59:24

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Have you ever had a case where you needed to find a process which sent a HUP/KILL/TERM or other signal to your database? Let me rephrase. Did you ever have to find which process messed up your night? 😉 If so, you might want to read on. I’m going to tell you how you can … Continued

## Structure detectee

- H2: Linux
- H2: FreeBSD/Solaris
- H3: SystemTap
- H4: Installing SystemTap
- H4: Tracing with SystemTap
- H3: Perf
- H4: Installing Perf
- H3: Audit
- H4: Installing Audit
- H2: Summary

## Auteur source

Robert joined the US consulting team of Percona in 2014. He is an avid fan of opensource and all things IT. Before joining Percona he worked in various roles for a Fortune 500 manufacturing company, most recently as an Infrastructure Architect / Engineer. He is originally from Europe, Hungary where he used to be an instructor and taught various Sun Microsystems classes across Europe. Robert has some academic background and he used to manage the network services of his old Department and his Dormitory. In those rare hours when he is not in front of a computer he spends time with his family or plays around with his RC gear.
