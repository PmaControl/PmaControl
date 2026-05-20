---
title: Migrating to XtraDB Cluster Webinar follow up questions
source:
  name: Percona Blog
  url: https://www.percona.com/blog/migrating-to-xtradb-cluster-webinar-follow-up-questions/
  post_id: 3623
source_author:
  name: Jay Janssen
  slug: jay-janssen
  url: https://www.percona.com/blog/author/jay-janssen/
  website: http://www.percona.com/
published_at: '2012-06-06T20:21:32'
published_at_gmt: '2012-06-06T20:21:32'
modified_at: '2026-03-23T22:22:00'
modified_at_gmt: '2026-03-23T22:22:00'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- XtraBackup
matched_filters:
- category:mysql:83
- search:percona-xtrabackup
- search:xtrabackup
categories:
- MySQL
- Percona Software
category_slugs:
- mysql
- percona-software
tags: []
tag_slugs: []
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Migrating to XtraDB Cluster Webinar follow up questions

Source: [Percona Blog](https://www.percona.com/blog/migrating-to-xtradb-cluster-webinar-follow-up-questions/)

Auteur source: [Jay Janssen](https://www.percona.com/blog/author/jay-janssen/)

Publication: 2012-06-06T20:21:32

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Thanks to all who attended my webinar today. The session was recorded and will be available to watch for free soon here. There were a lot of great questions asked during the session, so I’d like to take this opportunity to try to answer a few of them: Q: Is there an easy way to … Continued

## Structure detectee

- H2: Q: Is there an easy way to leverage the xtrabackup SST and IST in an xtradb cluster to take your full and incremental backups of the cluster’s databases?
- H2: Q: Can I run two Percona server node as Master-Master ?
- H2: Q: Are adding/updating mysql user accounts replicated?
- H2: Q: In the haproxy example, how do you specify with xtradb cluster the different ports for write and ready traffic?
- H2: Q: Any settings that require a mysql restart or can they all be updated with a reload?
- H2: Q: The SST does not work with rsync when the option innodb_data_home_dir is set to a different path then the datadir option. Any comments on this?
- H2: Q: what about humongous ALTER TABLE? Does it run one at a time in cluster and won’t affect overall availability?
- H2: Q: Does replication of MyISAM form any bottlenecks in XtraDB Cluster? If so, how bad?
- H2: There was a question about availability of Nagios or other monitoring plugins, and I misspoke:

## Auteur source

Jay joined Percona in 2011 after 7 years at Yahoo working in a variety of fields including High Availability architectures, MySQL training, tool building, global server load balancing, multi-datacenter environments, operationalization, and monitoring. He holds a B.S. of Computer Science from Rochester Institute of Technology.
