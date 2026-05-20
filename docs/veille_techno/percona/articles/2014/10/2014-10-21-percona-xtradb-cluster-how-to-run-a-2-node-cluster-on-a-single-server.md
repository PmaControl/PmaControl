---
title: 'Percona XtraDB Cluster: How to run a 2-node cluster on a single server'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/percona-xtradb-cluster-how-to-run-a-2-node-cluster-on-a-single-server/
  post_id: 8667
source_author:
  name: Fernando Laudares Camargos
  slug: fernando-laudares
  url: https://www.percona.com/blog/author/fernando-laudares/
  website: ''
published_at: '2014-10-21T13:53:05'
published_at_gmt: '2014-10-21T13:53:05'
modified_at: '2026-05-05T16:56:11'
modified_at_gmt: '2026-05-05T16:56:11'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- MySQL
- Percona Software
category_slugs:
- mysql
- percona-software
tags:
- datadirs
- Fernando Laudares
- MySQL
- mysqld_multi
- Percona XtraDB Cluster
- Primary
tag_slugs:
- datadirs
- fernando-laudares
- mysql
- mysqld_multi
- percona-xtradb-cluster
- primary
featured_image_url: ''
image_count: 0
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Percona XtraDB Cluster: How to run a 2-node cluster on a single server

Source: [Percona Blog](https://www.percona.com/blog/percona-xtradb-cluster-how-to-run-a-2-node-cluster-on-a-single-server/)

Auteur source: [Fernando Laudares Camargos](https://www.percona.com/blog/author/fernando-laudares/)

Publication: 2014-10-21T13:53:05

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I reckon there’s little sense in running 2 or more Percona XtraDB Cluster (PXC) nodes in a single physical server other than for educational and testing purposes – but doing so is still useful in those cases. The most popular way of achieving this seems to be with server virtualization, such as making use of … Continued

## Structure detectee

- H2: Which ports?
- H2: Installing Percona XtraDB Cluster, configuring and starting the first node
- H2: Configuring and starting the second node
- H2: Using mysqld_multi
- H2: Adding a second Percona XtraDB Cluster node to a production server
- H2: Additional ressources

## Auteur source

Fernando Laudares Camargos joined Percona in early 2013 after working 8 years for a Canadian company specialized in offering services based in open source technologies. Fernando's work experience includes the architecture, deployment and maintenance of IT infrastructures based on Linux, open source software and a layer of server virtualization. From the basic services such as DHCP & DNS to identity management systems, but also including backup routines, configuration management tools and thin-clients. He's now focusing on the universe of MySQL, MongoDB and PostgreSQL with a particular interest in understanding the intricacies of database systems and contributes regularly to this blog. You can read his other articles here.
