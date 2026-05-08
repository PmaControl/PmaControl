---
title: Percona Server for MySQL in Docker Swarm with Secrets
source:
  name: Percona Blog
  url: https://www.percona.com/blog/percona-server-for-mysql-in-docker-swarm-with-secrets/
  post_id: 16720
source_author:
  name: Andrew Moore
  slug: amoore
  url: https://www.percona.com/blog/author/amoore/
  website: ''
published_at: '2017-04-21T21:43:39'
published_at_gmt: '2017-04-21T21:43:39'
modified_at: '2026-05-05T20:01:46'
modified_at_gmt: '2026-05-05T20:01:46'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
matched_filters:
- category:mysql:83
categories:
- Cloud
- MySQL
- Security
category_slugs:
- cloud
- mysql
- security
tags:
- Docker Swarm
- MySQL
- Percona Server for MySQL
- security
tag_slugs:
- docker-swarm
- mysql
- percona-server
- security
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Percona-Server-for-MySQL-in-Docker-Swarm.jpg
image_count: 1
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Percona Server for MySQL in Docker Swarm with Secrets

Source: [Percona Blog](https://www.percona.com/blog/percona-server-for-mysql-in-docker-swarm-with-secrets/)

Auteur source: [Andrew Moore](https://www.percona.com/blog/author/amoore/)

Publication: 2017-04-21T21:43:39

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This quick post demonstrates using Percona Server for MySQL in Docker Swarm with some new authentication provisioning practices. Some small changes to the startup script for the Percona-Server container image allows us to specify a file that contains password values to set as our root user’s secret. “Why do we need this functionality,” I hear … Continued

## Structure detectee

- H3: Environment Variables
- H3: Environment File
- H3: Password File
- H3: Docker Secrets

## Images et graphiques reperes

- featured / image: [Percona Server for MySQL in Docker Swarm with Secrets](https://www.percona.com/wp-content/uploads/2026/03/Percona-Server-for-MySQL-in-Docker-Swarm.jpg)

## Auteur source

Since fall 2013, Andrew has been working within Percona's Remote DBA team plying his experience to the client's environments and internal tools developed to keep operations slick. He lives in the UK with his young family and loves to complain about the less than perfect climate. Andrew makes time to pursue an amateur soccer career but won't be trading in MySQL any time soon.
