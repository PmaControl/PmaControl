---
title: Paul McCullagh answers your questions about PBXT
source:
  name: Percona Blog
  url: https://www.percona.com/blog/paul-mccullagh-answers-your-questions-about-pbxt/
  post_id: 2137
source_author:
  name: Morgan Tocker
  slug: morgan
  url: https://www.percona.com/blog/author/morgan/
  website: http://www.percona.com/
published_at: '2009-11-20T19:29:55'
published_at_gmt: '2009-11-20T19:29:55'
modified_at: '2026-03-23T21:36:25'
modified_at_gmt: '2026-03-23T21:36:25'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Insight for Developers
- MySQL
category_slugs:
- insight-for-developers
- mysql
tags:
- Community
- interview
- MariaDB
- paul mccullagh
- PBXT
- Storage Engine
tag_slugs:
- community
- interview
- mariadb
- paul-mccullagh
- pbxt
- storage-engine
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Paul McCullagh answers your questions about PBXT

Source: [Percona Blog](https://www.percona.com/blog/paul-mccullagh-answers-your-questions-about-pbxt/)

Auteur source: [Morgan Tocker](https://www.percona.com/blog/author/morgan/)

Publication: 2009-11-20T19:29:55

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Following on from our earlier announcement, Paul McCullagh has responded with the answers to your questions – as well as a few I gathered from other Percona folks, and attendees of OpenSQL Camp. Thank you Paul! Whatâ€™s the “ideal” use case for the PBXT engine, and how does it compare in performance? Â When would I … Continued

## Structure detectee

- H3: Whatâ€™s the “ideal” use case for the PBXT engine, and how does it compare in performance? Â When would I use PBXT instead of a storage engine like MyISAM, InnoDB or XtraDB?
- H3: I think I remember reports that PBXT (at an early stage) out performed InnoDB with INSERTS and UPDATES (but not SELECTS). That would make PBXT very interesting for non-SELECT-intensive applications (finance, production management etc.) in my opinion. Â Is this the case, and do you have any recent benchmarks available?
- H3: What were the hard decisions or trade-offs that you had to make when designing PBXT?
- H3: How does online backup work in PBXT, and is incremental backup possible?
- H3: Does PBXT have a maintenance thread like InnoDB’s main thread?
- H3: Does PBXT support clustered indexes?
- H3: What is the page size in PBXT, and can it be tuned?
- H3: Are there any differences in the PBXT implementation of MVCC that might surprise experienced InnoDB DBAs? Â Also – In MVCC does it keep the versions in indexes, and can PBXT use MVCC for index scans?
- H3: PBXT supports row-level locking and foreign keys. Does this create any additional locking overhead that we should be aware of?
- H3: When I evaluate a storage engine my key acceptance criteria are things like backup, concurrency, ACID compliance and crash recovery. As a storage engine developer, what other criteria do you think I should be adding?
- H3: MySQL supports a “pluggable storage engine API”, but it seems that not all the storage engine vendors are able to keep all their code at that layer (Infobright had to make major changes to MySQL itself). Â What war stories can you report on in plugging into MySQL?
- H3: PBXT seems to have very few configuration parameters. Â Was this an intentional design decision, and do you see it creating opportunities for you in organizations with less internal IT-expertise?

## Auteur source

Morgan is a former Percona employee. He was the Director of Training at Percona. He was formerly a Technical Instructor for MySQL and Sun Microsystems. He has also previously worked in the MySQL Support Team, and provided DRBD support.
