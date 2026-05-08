---
title: Managing Time Series Data Using TimeScaleDB-Powered PostgreSQL
source:
  name: Percona Blog
  url: https://www.percona.com/blog/managing-time-series-data-using-timescaledb-powered-postgresql/
  post_id: 28163
source_author:
  name: Robert Bernier
  slug: robert-bernier
  url: https://www.percona.com/blog/author/robert-bernier/
  website: ''
published_at: '2024-03-12T14:05:08'
published_at_gmt: '2024-03-12T14:05:08'
modified_at: '2026-03-26T20:07:34'
modified_at_gmt: '2026-03-26T20:07:34'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- Percona Toolkit
matched_filters:
- search:percona-toolkit
categories:
- Insight for DBAs
- Insight for Developers
- PostgreSQL
category_slugs:
- insight-for-dbas
- insight-for-developers
- postgresql
tags:
- PostgreSQL
- Robert PP
tag_slugs:
- postgresql
- robert-planetpostgresql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Time-Series-Data-Using-TimeScaleDB-Powered-PostgreSQL.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Managing Time Series Data Using TimeScaleDB-Powered PostgreSQL

Source: [Percona Blog](https://www.percona.com/blog/managing-time-series-data-using-timescaledb-powered-postgresql/)

Auteur source: [Robert Bernier](https://www.percona.com/blog/author/robert-bernier/)

Publication: 2024-03-12T14:05:08

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

PostgreSQL extensions are great! Simply by adding an extension, one transforms what is an otherwise vanilla general-purpose database management system into one capable of processing data requirements in a highly optimized fashion. Some extensions, like pg_repack, simplify and enhance existing features already, while other extensions, such as PostGIS and pgvector, add completely new capabilities. I’d … Continued

## Structure detectee

- H2: Installing and enabling the TimescaleDB extension
- H2: Step one: Create the PostgreSQL file repository configuration
- H2: Step two: Get the extension
- H2: Step three: Install TimescaleDB packages
- H2: Step four: Tune the data-cluster
- H2: Step five: Create database and extension
- H2: Working with Timescale
- H2: Scenario one
- H3: Creating the tables
- H3: Increasing/decreasing chunk size
- H3: Populating the tables
- H2: Administering hypertable chunks
- H3: Chunk, general purpose function calls
- H3: Chunk, compression function calls
- H3: Timescale chunk runtime parameters
- H2: Scenario two
- H3: Compressing a chunk
- H3: Decompressing a chunk
- H3: Setting a chunk compression policy
- H2: Caveat
- H3: References

## Images et graphiques reperes

- featured / image: [Managing Time Series Data Using TimeScaleDB-Powered PostgreSQL](https://www.percona.com/wp-content/uploads/2026/03/Time-Series-Data-Using-TimeScaleDB-Powered-PostgreSQL.jpg)

## Auteur source

Robert's first working computer was the very user-friendly IBM 360 with an awesome 4MB RAM. After a round of much needed therapy overcoming the trauma of programming with punch cards he discovered the IBM-XT and the miracle of DOS 2.0. Years later, Robert became enamored with Linux and the opensource world and after meeting one of the members of CORE his primary focus had become all things PostgreSQL. Robert has since then worked in mom and pop companies, fortune 50 corporations and a number of very cool environments including the famed Los Alamos National Laboratory in New Mexico, birthplace of the atomic age. Although reluctant to leave the enjoyable experience of California's Silicon Valley commuter life, he returned to the Pacific Northwest and once again experienced real weather. These days, he serves as the PostgreSQL Consultant here at Percona.
