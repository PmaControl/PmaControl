---
title: 'Yearly Reminder: DDLs That Fail to Propagate May Cause Percona XtraDB Cluster Inconsistencies'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/yearly-reminder-ddls-that-fail-to-propagate-may-cause-percona-xtradb-cluster-inconsistencies/
  post_id: 24160
source_author:
  name: Fernando Laudares Camargos
  slug: fernando-laudares
  url: https://www.percona.com/blog/author/fernando-laudares/
  website: ''
published_at: '2021-04-23T16:55:30'
published_at_gmt: '2021-04-23T16:55:30'
modified_at: '2026-04-27T22:25:54'
modified_at_gmt: '2026-04-27T22:25:54'
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
- Percona Software
category_slugs:
- insight-for-dbas
- insight-for-developers
- mysql
- percona-software
tags:
- Galera Cluster
- MySQL
- mysql-and-variants
- Percona XtraDB Cluster
- pxc
tag_slugs:
- galera-cluster
- mysql
- mysql-and-variants
- percona-xtradb-cluster
- pxc
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/DDLs-Percona-XtraDB-Cluster-Inconsistencies.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Yearly Reminder: DDLs That Fail to Propagate May Cause Percona XtraDB Cluster Inconsistencies

Source: [Percona Blog](https://www.percona.com/blog/yearly-reminder-ddls-that-fail-to-propagate-may-cause-percona-xtradb-cluster-inconsistencies/)

Auteur source: [Fernando Laudares Camargos](https://www.percona.com/blog/author/fernando-laudares/)

Publication: 2021-04-23T16:55:30

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Apologies for the silly title, but the issue is a real one, even though it is not a new thing. Schema upgrades are not an ordinary operation in Galera. For the subject at hand, the bottom line is: under the default Total Order Isolation (TOI) method, “the cluster replicates the schema change query as a … Continued

## Structure detectee

- H2: How Big of an Issue Is This?
- H2: How Does the Problem Manifest Itself in Practice? Give Us an Example!

## Images et graphiques reperes

- featured / image: [Yearly Reminder: DDLs That Fail to Propagate May Cause Percona XtraDB Cluster Inconsistencies](https://www.percona.com/wp-content/uploads/2026/03/DDLs-Percona-XtraDB-Cluster-Inconsistencies.png)
- content / image: [DDLs Percona XtraDB Cluster Inconsistencies](https://www.percona.com/wp-content/uploads/2026/03/DDLs-Percona-XtraDB-Cluster-Inconsistencies-300x157.png)

## Auteur source

Fernando Laudares Camargos joined Percona in early 2013 after working 8 years for a Canadian company specialized in offering services based in open source technologies. Fernando's work experience includes the architecture, deployment and maintenance of IT infrastructures based on Linux, open source software and a layer of server virtualization. From the basic services such as DHCP & DNS to identity management systems, but also including backup routines, configuration management tools and thin-clients. He's now focusing on the universe of MySQL, MongoDB and PostgreSQL with a particular interest in understanding the intricacies of database systems and contributes regularly to this blog. You can read his other articles here.
