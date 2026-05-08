---
title: A closer look at the MySQL ibdata1 disk space issue and big tables
source:
  name: Percona Blog
  url: https://www.percona.com/blog/the-mysql-ibdata1-disk-space-issue-and-big-tables-part-1/
  post_id: 8424
source_author:
  name: Fernando Laudares Camargos
  slug: fernando-laudares
  url: https://www.percona.com/blog/author/fernando-laudares/
  website: ''
published_at: '2014-08-21T15:24:52'
published_at_gmt: '2014-08-21T15:24:52'
modified_at: '2026-03-25T17:38:45'
modified_at_gmt: '2026-03-25T17:38:45'
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
- Benchmarks
- Insight for DBAs
- MySQL
- Percona Services
category_slugs:
- benchmarks
- insight-for-dbas
- mysql
- percona-services
tags:
- disk space issue
- Fernando Laudares
- ibdata1
- InnoDB
- shared tablespace*
tag_slugs:
- disk-space-issue
- fernando-laudares
- ibdata1
- innodb
- shared-tablespace
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# A closer look at the MySQL ibdata1 disk space issue and big tables

Source: [Percona Blog](https://www.percona.com/blog/the-mysql-ibdata1-disk-space-issue-and-big-tables-part-1/)

Auteur source: [Fernando Laudares Camargos](https://www.percona.com/blog/author/fernando-laudares/)

Publication: 2014-08-21T15:24:52

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

A recurring and very common customer issue seen here at the Percona Support team involves how to make the ibdata1 file “shrink” within MySQL. I can only imagine there’s a degree of regret by some of the InnoDB architects on their design decisions regarding disk-space management by the shared tablespace* because this has been a big … Continued

## Structure detectee

- H2: A big table scenario
- H2: What to do then ?
- H2: Conclusion

## Auteur source

Fernando Laudares Camargos joined Percona in early 2013 after working 8 years for a Canadian company specialized in offering services based in open source technologies. Fernando's work experience includes the architecture, deployment and maintenance of IT infrastructures based on Linux, open source software and a layer of server virtualization. From the basic services such as DHCP & DNS to identity management systems, but also including backup routines, configuration management tools and thin-clients. He's now focusing on the universe of MySQL, MongoDB and PostgreSQL with a particular interest in understanding the intricacies of database systems and contributes regularly to this blog. You can read his other articles here.
