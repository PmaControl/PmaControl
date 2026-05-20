---
title: 'SET PERSIST in MySQL: A Small Thing for Setting System Variable Values'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/set-persist-in-mysql/
  post_id: 20816
source_author:
  name: Marco Tusa
  slug: tusa
  url: https://www.percona.com/blog/author/tusa/
  website: ''
published_at: '2019-08-13T14:24:56'
published_at_gmt: '2019-08-13T14:24:56'
modified_at: '2026-04-27T21:20:46'
modified_at_gmt: '2026-04-27T21:20:46'
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
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- DBA
- MySQL
tag_slugs:
- dba
- mysql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/SET-PERSIST-in-MySQL.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# SET PERSIST in MySQL: A Small Thing for Setting System Variable Values

Source: [Percona Blog](https://www.percona.com/blog/set-persist-in-mysql/)

Auteur source: [Marco Tusa](https://www.percona.com/blog/author/tusa/)

Publication: 2019-08-13T14:24:56

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

To set correct system variable values is the essential step to get the correct server behavior against the workload. In MySQL, we have many System variables that can be changed at runtime, and most of them can be set at the session or global level. To change the value of a system variable at the … Continued

## Structure detectee

- H3: What’s new in MySQL8 about that?
- H2: The new option for SET command is PERSIST
- H3: Anyhow, why is this a good thing to have?
- H3: A short deep dive in the code (you can jump it if you don’t care)
- H2: SET PERSIST Conclusion
- H2: References:

## Images et graphiques reperes

- featured / image: [SET PERSIST in MySQL: A Small Thing for Setting System Variable Values](https://www.percona.com/wp-content/uploads/2026/03/SET-PERSIST-in-MySQL.jpg)
- content / image: [SET PERSIST in MySQL](https://www.percona.com/wp-content/uploads/2026/03/small_things-300x300.jpg)

## Auteur source

Marco Tusa had his own international practice for the past twenty eight years. His experience and expertise are in a wide variety of information technology and information management fields, cover research, development, analysis, quality control, project management and team management. Marco is currently working at Percona as the MySQL Tech Lead, previously working at Percona as Manager of the Consulting Rapid Response Team on October 2013. He has being working as employee for the SUN Microsystems as MySQL Professional Service manager for South Europe., and previously in MySQL AB. He has worked with the Food and Agriculture Organization of the United Nation since 1994, leading the development of the Organization’s hyper textual environment.Team leader for the FAO corporate database support. For several years he has led the development group in the WAICENT/Faoinfo team. He has assisted in defining the Organization’s guidelines for the dissemination of information from the technology and the management point of view. He has participated in field missions in order to perform analysis, reviews and evaluation of the status of local projects, providing local support and advice. He had collaborates with MIT Media Lab (Massachusetts Institute of Technology laboratory) and FAO as Sustainable Information Technology for developing countries Specialist in relation with the FAO’s Special Program for Food Security for Senegal.
