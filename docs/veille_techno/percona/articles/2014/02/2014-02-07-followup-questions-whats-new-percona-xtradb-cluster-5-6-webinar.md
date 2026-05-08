---
title: Followup questions to ‘What’s new in Percona XtraDB Cluster 5.6’ webinar
source:
  name: Percona Blog
  url: https://www.percona.com/blog/followup-questions-whats-new-percona-xtradb-cluster-5-6-webinar/
  post_id: 7804
source_author:
  name: Jay Janssen
  slug: jay-janssen
  url: https://www.percona.com/blog/author/jay-janssen/
  website: http://www.percona.com/
published_at: '2014-02-07T14:00:53'
published_at_gmt: '2014-02-07T14:00:53'
modified_at: '2026-04-28T22:01:39'
modified_at_gmt: '2026-04-28T22:01:39'
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
- Webinars
category_slugs:
- mysql
- webinars
tags:
- Jay Janssen
- Percona XtraDB Cluster 5.6
tag_slugs:
- jay-janssen
- percona-xtradb-cluster-5-6
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Followup questions to ‘What’s new in Percona XtraDB Cluster 5.6’ webinar

Source: [Percona Blog](https://www.percona.com/blog/followup-questions-whats-new-percona-xtradb-cluster-5-6-webinar/)

Auteur source: [Jay Janssen](https://www.percona.com/blog/author/jay-janssen/)

Publication: 2014-02-07T14:00:53

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Thanks to all who attended my webinar yesterday. The slides and recording are available on the webinar’s page. I was a bit overwhelmed with the amount of questions that came in and I’ll try to answer them the best I can here. Q: Does Percona XtraDB Cluster support writing to multiple master? Yes, it does. … Continued

## Structure detectee

- H3: Q: Does Percona XtraDB Cluster support writing to multiple master?
- H3: Q: Is there any limitation to scale writes?
- H3: Q: Are the WAN segments feature only used to reduce network bandwidth? Would DB write performance be the same before Galera 3.x since each commit still has to be ack by all the servers across the WAN?
- H3: Q: What is the max number of cluster servers across a WAN recommended before you start seeing a diminishing return in performance b/c of sync replication?
- H3: Q: Should I be worried about the auto_increment bug you mentioned? I wasn’t planning to upgrade our cluster to Percona XtraDB Cluster 5.6 soon.
- H3: Q: Does Percona XtraDB Cluster support SphinxSE Storage Engine?
- H3: Q: To convert from mysql to Percona XtraDB Cluster 5.6, do you now recommend first upgrading to mysql 5.6 and then converting to PXC?
- H3: Q: Do “WAN segments” effect the quorum in any way?
- H3: Q: Since each galera node is identical, they all have the same storage footprint. What are best practices for expanding storage on galera nodes when we are low on free space?
- H3: Q: Is there any change to wsrep_retry_autocommit behavior in Percona XtraDB Cluster 5.6, or any plans to apply this setting to explicit transactions in order to avoid cluster “deadlocks”?
- H3: Q: I heard about xtrabackup-v2. What is the difference between the previous one?
- H3: Q: Can a cluster which use rsync to be switched to xtrabackup in a rolling like mode?
- H3: Q: Do you saw or installed or recommended Percona XtraDB Cluster 5.6 for production now or wait for a while ?
- H3: Q: are there any specific warnings or cautions to be aware of with PXC 5.6 when the db uses triggers and/or procedures, beyond the cautions in MySQL itself?
- H3: Q: So all the certifications come directly from the applying nodes back to the node that sent the data? Or does it relay back through the relay node?
- H3: Q: we are on Percona XtraDB Cluster 5.5 and have bursts of a large number of simultaneous updates to the same table which often triggers lock wait timeouts. Could binlog_row_image=minimal help reduce the frequency of these lock waits?
- H3: Q: Any Percona XtraDB Cluster 5.6 or Galera 3.x settings to increase write performance across the cluster as well as clusters in different geographic locations?

## Auteur source

Jay joined Percona in 2011 after 7 years at Yahoo working in a variety of fields including High Availability architectures, MySQL training, tool building, global server load balancing, multi-datacenter environments, operationalization, and monitoring. He holds a B.S. of Computer Science from Rochester Institute of Technology.
