---
title: 'Q&A: Percona XtraDB Cluster as a MySQL HA solution for OpenStack'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/qa-percona-xtradb-cluster-as-a-mysql-ha-solution-for-openstack/
  post_id: 8755
source_author:
  name: Jay Janssen
  slug: jay-janssen
  url: https://www.percona.com/blog/author/jay-janssen/
  website: http://www.percona.com/
published_at: '2014-11-14T14:00:44'
published_at_gmt: '2014-11-14T14:00:44'
modified_at: '2026-03-25T17:46:02'
modified_at_gmt: '2026-03-25T17:46:02'
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
- Percona Software
category_slugs:
- insight-for-dbas
- mysql
- percona-software
tags:
- galera
- HA
- High Availability
- Jay Janssen
- MySQL
- OpenStack
- Percona XtraDB Cluster
- Secondary
- Trove
tag_slugs:
- galera
- ha
- high-availability
- jay-janssen
- mysql
- openstack
- percona-xtradb-cluster
- secondary
- trove
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/QA-Percona-XtraDB-Cluster-as-a-MySQL-HA-solution-for-Openstack.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Q&A: Percona XtraDB Cluster as a MySQL HA solution for OpenStack

Source: [Percona Blog](https://www.percona.com/blog/qa-percona-xtradb-cluster-as-a-mysql-ha-solution-for-openstack/)

Auteur source: [Jay Janssen](https://www.percona.com/blog/author/jay-janssen/)

Publication: 2014-11-14T14:00:44

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Thanks to all who attended my Nov. 12 webinar titled, “Percona XtraDB Cluster as a MySQL HA Solution for OpenStack.” I had several questions which I covered at the end and a few that I didn’t. You can view the entire webinar and download the slides here. Q: Is the read,write speed reduced in Galera … Continued

## Structure detectee

- H3: Q: Is the read,write speed reduced in Galera compared to normal MySQL?
- H3: Q: Does state transfers affect a continuing transaction within the nodes?
- H3: Q: Perhaps not the correct webinar for this question, but I was also expecting to hear something about using PXC in combination with OpenStack Trove. If you’ve got time, could you tell something about that?
- H3: Q: For Loadbalancing using the Java Mysql driver, would you suggest HA proxy or the loadbalancing connection in the java driver. Also how does things work in case of persistent connections and connection pools like dbcp?
- H2: Q: Are there any manual solutions to avoid deadlocks within a high write context to force a query to execute on all nodes?

## Images et graphiques reperes

- featured / image: [Q&A: Percona XtraDB Cluster as a MySQL HA solution for OpenStack](https://www.percona.com/wp-content/uploads/2026/03/QA-Percona-XtraDB-Cluster-as-a-MySQL-HA-solution-for-Openstack.jpg)
- content / image: [Q&A: Percona XtraDB Cluster as a MySQL HA solution for Openstack](https://www.percona.com/wp-content/uploads/2026/03/QA-Percona-XtraDB-Cluster-as-a-MySQL-HA-solution-for-Openstack-300x199.jpg)

## Auteur source

Jay joined Percona in 2011 after 7 years at Yahoo working in a variety of fields including High Availability architectures, MySQL training, tool building, global server load balancing, multi-datacenter environments, operationalization, and monitoring. He holds a B.S. of Computer Science from Rochester Institute of Technology.
