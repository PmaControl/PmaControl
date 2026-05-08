---
title: 'Swimming with Sharks: Analyzing Encrypted Database Traffic Using Wireshark'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/swimming-with-sharks-analyzing-encrypted-database-traffic-using-wireshark/
  post_id: 35244
source_author:
  name: Pep Pla
  slug: pep-pla
  url: https://www.percona.com/blog/author/pep-pla/
  website: ''
published_at: '2025-09-08T13:28:25'
published_at_gmt: '2025-09-08T13:28:25'
modified_at: '2026-03-26T20:25:18'
modified_at_gmt: '2026-03-26T20:25:18'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- Percona Toolkit
matched_filters:
- category:mysql:83
- search:percona-toolkit
categories:
- Insight for DBAs
- MongoDB
- MySQL
- PostgreSQL
- Security
category_slugs:
- insight-for-dbas
- mongodb
- mysql
- postgresql
- security
tags:
- MongoDB
- MySQL
- PostgreSQL
- security
tag_slugs:
- mongodb
- mysql
- postgresql
- security
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Analyzing-Encrypted-Database-Traffic-Using-Wireshark.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Swimming with Sharks: Analyzing Encrypted Database Traffic Using Wireshark

Source: [Percona Blog](https://www.percona.com/blog/swimming-with-sharks-analyzing-encrypted-database-traffic-using-wireshark/)

Auteur source: [Pep Pla](https://www.percona.com/blog/author/pep-pla/)

Publication: 2025-09-08T13:28:25

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Percona has a great set of tools known as the Percona Toolkit, one of which is pt-upgrade. The idea behind this tool is to replay a captured sequence of queries that were executed on a different database server. This is very useful to validate if a new version of the database server works as expected … Continued

## Structure detectee

- H2: How does TLS encryption work?
- H2: What is a Dynamic Library Shim?
- H2: How does it work?
- H2: Writing the shim library to capture TLS keys
- H2: Compiling the shim shared library
- H2: Let’s capture some traffic and decrypt it using Wireshark
- H3: Terminal 1: Running Wireshark
- H3: Terminal 2: Running the PostgreSQL client with the shim library
- H3: Terminal 1: Stop Wireshark
- H2: Analyzing the captured traffic
- H2: Analyzing the traffic using Wireshark GUI

## Images et graphiques reperes

- featured / image: [Swimming with Sharks: Analyzing Encrypted Database Traffic Using Wireshark](https://www.percona.com/wp-content/uploads/2026/03/Analyzing-Encrypted-Database-Traffic-Using-Wireshark.jpg)
- content / image: [wireshark preferences](https://www.percona.com/wp-content/uploads/2026/03/Swimming-with-sharks.png)

## Auteur source

Pep has been working with databases all his life. Born in a small village by the Mediterranean, he currently lives in Barcelona. He loves tech, traveling, good food, music and, all things NASA. He hates talking about himself in the third person and has a particular sense of humor. Happily married, he is the father of three boys and three cats.
