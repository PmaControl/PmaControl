---
title: When (and how) to move an InnoDB table outside the shared tablespace
source:
  name: Percona Blog
  url: https://www.percona.com/blog/when-and-how-to-move-an-innodb-table-outside-the-shared-tablespace/
  post_id: 8450
source_author:
  name: Fernando Laudares Camargos
  slug: fernando-laudares
  url: https://www.percona.com/blog/author/fernando-laudares/
  website: ''
published_at: '2014-08-22T14:29:46'
published_at_gmt: '2014-08-22T14:29:46'
modified_at: '2026-04-28T22:10:07'
modified_at_gmt: '2026-04-28T22:10:07'
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
- Percona Services
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-services
- percona-software
tags:
- Disk space
- ibdata1
- InnoDB table
- Percona Server for MySQL
- shared tablespace*
- TokuDB
tag_slugs:
- disk-space
- ibdata1
- innodb-table
- percona-server
- shared-tablespace
- tokudb
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# When (and how) to move an InnoDB table outside the shared tablespace

Source: [Percona Blog](https://www.percona.com/blog/when-and-how-to-move-an-innodb-table-outside-the-shared-tablespace/)

Auteur source: [Fernando Laudares Camargos](https://www.percona.com/blog/author/fernando-laudares/)

Publication: 2014-08-22T14:29:46

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In my last post, “ For example, when you run a transaction involving InnoDB tables, MySQL will first write all the changes it triggers in an undo log, for the case you later decide to “roll them back”. Long standing, uncommited transactions are one of the causes for a growing ibdata file. Of course, if … Continued

## Structure detectee

- H2: The experiment
- H3: #1) Converting to MyISAM
- H3: #2) Exporting the table to a private tablespace
- H3: #3) Dump and restore: looking at the disk space use
- H3: #4) Compressing the table
- H3: #5) Converting the table to TokuDB
- H3: #6) Expanding the shared tablespace
- H2: Conclusion

## Auteur source

Fernando Laudares Camargos joined Percona in early 2013 after working 8 years for a Canadian company specialized in offering services based in open source technologies. Fernando's work experience includes the architecture, deployment and maintenance of IT infrastructures based on Linux, open source software and a layer of server virtualization. From the basic services such as DHCP & DNS to identity management systems, but also including backup routines, configuration management tools and thin-clients. He's now focusing on the universe of MySQL, MongoDB and PostgreSQL with a particular interest in understanding the intricacies of database systems and contributes regularly to this blog. You can read his other articles here.
