---
title: Implementing SchemaSpy in your MySQL environment
source:
  name: Percona Blog
  url: https://www.percona.com/blog/implementing-schemaspy-in-your-mysql-environment/
  post_id: 6877
source_author:
  name: Michael Coburn
  slug: michael-coburn
  url: https://www.percona.com/blog/author/michael-coburn/
  website: ''
published_at: '2013-06-04T10:00:47'
published_at_gmt: '2013-06-04T10:00:47'
modified_at: '2026-05-05T22:48:21'
modified_at_gmt: '2026-05-05T22:48:21'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Insight for DBAs
- Insight for Developers
- MySQL
category_slugs:
- insight-for-dbas
- insight-for-developers
- mysql
tags:
- Michael Coburn
- SchemaSpy
tag_slugs:
- michael-coburn
- schemaspy
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/FK_only.png
image_count: 5
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Implementing SchemaSpy in your MySQL environment

Source: [Percona Blog](https://www.percona.com/blog/implementing-schemaspy-in-your-mysql-environment/)

Auteur source: [Michael Coburn](https://www.percona.com/blog/author/michael-coburn/)

Publication: 2013-06-04T10:00:47

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Lately I have been working with a set of customers on a longer term basis which has given me time to explore new tools using their environments. One tool that I am finding very helpful is called SchemaSpy. SchemaSpy is a Java-based tool (requires Java 5 or higher) that analyzes the metadata of a schema … Continued

## Structure detectee

- H2: Installation of SchemaSpy and Dependencies
- H2: Creating a mysql.properties file
- H2: Example Schema
- H2: Running SchemaSpy (with Foreign Keys)
- H2: Creating a Metadata File
- H2: Running SchemaSpy
- H2: Viewing SchemaSpy output
- H2: Final Thoughts

## Images et graphiques reperes

- featured / image: [Implementing SchemaSpy in your MySQL environment](https://www.percona.com/wp-content/uploads/2026/03/FK_only.png)
- content / image: [SchemaSpy](https://www.percona.com/wp-content/uploads/2026/03/SchemaSpy-300x225.jpg)
- content / image: [FK_only](https://www.percona.com/wp-content/uploads/2026/03/FK_only1.png)
- content / image: [implied](https://www.percona.com/wp-content/uploads/2026/03/implied.png)
- content / image: [after_metadata_application](https://www.percona.com/wp-content/uploads/2026/03/after_metadata_application.png)

## Auteur source

Michael Coburn works at Percona on the Professional Services team in the role of Principal Architect. Michael joined Percona in 2012 as a Consultant after having worked as a DBA with stock photography websites and email service provider platforms. With a foundation in Systems Administration, Michael previously served as Product Manager responsible for Percona Monitoring and Management (PMM).
