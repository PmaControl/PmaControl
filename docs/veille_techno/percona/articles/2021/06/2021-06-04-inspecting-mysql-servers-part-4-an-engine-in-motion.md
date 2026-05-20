---
title: 'Inspecting MySQL Servers Part 4: An Engine in Motion'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/inspecting-mysql-servers-part-4-an-engine-in-motion/
  post_id: 24424
source_author:
  name: Fernando Laudares Camargos
  slug: fernando-laudares
  url: https://www.percona.com/blog/author/fernando-laudares/
  website: ''
published_at: '2021-06-04T12:27:58'
published_at_gmt: '2021-06-04T12:27:58'
modified_at: '2026-04-28T01:14:47'
modified_at_gmt: '2026-04-28T01:14:47'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
- Percona Toolkit
matched_filters:
- category:mysql:83
- search:percona-monitoring-and-management
- search:percona-toolkit
- search:pmm
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- MySQL
- mysql-and-variants
- Percona Software
tag_slugs:
- mysql
- mysql-and-variants
- percona-software
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/pt-stalk-MySQL-Percona.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Inspecting MySQL Servers Part 4: An Engine in Motion

Source: [Percona Blog](https://www.percona.com/blog/inspecting-mysql-servers-part-4-an-engine-in-motion/)

Auteur source: [Fernando Laudares Camargos](https://www.percona.com/blog/author/fernando-laudares/)

Publication: 2021-06-04T12:27:58

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

The combination of the information obtained from the “pt-summaries” discussed in the previous posts of this series (Part 1: The Percona Support Way, Part 2: Knowing the Server, Part 3: What MySQL?) helps us come up with the first impression of a MySQL server. However, apart from the quick glance we get at two samples … Continued

## Structure detectee

- H2: A Mix of OS and MySQL Diagnostics Data
- H2: Was the Server Under Moderate or High Load When the Data was Captured?
- H2: Depicting CPU Usage and I/O
- H2: MySQL in Motion
- H3: Does My Hot Data Fit in Memory?
- H3: Looking at Temporary Tables
- H3: Contention at the Table Cache Level
- H2: Looking Inside the (Real) Engine
- H3: Transactions Running
- H3: Long-Running Transactions
- H3: Checkpointing and Redo Log Space
- H3: Adaptive Hash Index
- H2: A Quick Note on Processlist
- H2: Daemon Mode
- H3: In the Next Post …

## Images et graphiques reperes

- featured / image: [Inspecting MySQL Servers Part 4: An Engine in Motion](https://www.percona.com/wp-content/uploads/2026/03/pt-stalk-MySQL-Percona.png)
- content / image: [pt-stalk MySQL Percona](https://www.percona.com/wp-content/uploads/2026/04/pt-stalk-MySQL-Percona-300x169-1.png)

## Auteur source

Fernando Laudares Camargos joined Percona in early 2013 after working 8 years for a Canadian company specialized in offering services based in open source technologies. Fernando's work experience includes the architecture, deployment and maintenance of IT infrastructures based on Linux, open source software and a layer of server virtualization. From the basic services such as DHCP & DNS to identity management systems, but also including backup routines, configuration management tools and thin-clients. He's now focusing on the universe of MySQL, MongoDB and PostgreSQL with a particular interest in understanding the intricacies of database systems and contributes regularly to this blog. You can read his other articles here.
