---
title: Simplified Percona XtraDB Cluster SSL Configuration
source:
  name: Percona Blog
  url: https://www.percona.com/blog/simplified-percona-xtradb-cluster-ssl-configuration/
  post_id: 16727
source_author:
  name: Kenn Takara
  slug: kenn-takara
  url: https://www.percona.com/blog/author/kenn-takara/
  website: ''
published_at: '2017-04-21T17:09:54'
published_at_gmt: '2017-04-21T17:09:54'
modified_at: '2026-05-05T18:37:16'
modified_at_gmt: '2026-05-05T18:37:16'
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
- Security
category_slugs:
- mysql
- percona-software
- security
tags:
- MySQL
- MySQL-SSL Connections
- Percona XtraDB Cluster
- SSL
tag_slugs:
- mysql
- mysql-ssl-connections
- percona-xtradb-cluster
- ssl
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraDB-Cluster-certification-1-e1492634721934.png
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Simplified Percona XtraDB Cluster SSL Configuration

Source: [Percona Blog](https://www.percona.com/blog/simplified-percona-xtradb-cluster-ssl-configuration/)

Auteur source: [Kenn Takara](https://www.percona.com/blog/author/kenn-takara/)

Publication: 2017-04-21T17:09:54

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, we’ll look at a feature that recently added to Percona XtraDB Cluster 5.7.16, that makes it easier to configure Percona XtraDB Cluster SSL for all related communications. It uses mode “encrypt=4”, and configures SSL for both IST/Galera communications and SST communications using the same SSL files. “encrypt=4” is a new encryption … Continued

## Structure detectee

- H3: Example
- H4: Step 1: Configuration (on all nodes)
- H4: Step 2: Startup the bootstrap node
- H4: Step 3: Copy the SSL files to all other nodes
- H4: Step 4: Startup the other nodes
- H3: Customization
- H3: How it works

## Images et graphiques reperes

- featured / image: [Simplified Percona XtraDB Cluster SSL Configuration](https://www.percona.com/wp-content/uploads/2026/03/Percona-XtraDB-Cluster-certification-1-e1492634721934.png)
