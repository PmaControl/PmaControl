---
title: 'PMM Server + podman: Running a Container Without root Privileges'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/pmm-server-podman-running-a-container-without-root-privileges/
  post_id: 21101
source_author:
  name: Ceri Williams
  slug: ceri-williams
  url: https://www.percona.com/blog/author/ceri-williams/
  website: https://www.percona.com
published_at: '2019-10-22T14:43:07'
published_at_gmt: '2019-10-22T14:43:07'
modified_at: '2026-04-27T21:24:59'
modified_at_gmt: '2026-04-27T21:24:59'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- PMM
matched_filters:
- category:monitoring:2104
- search:percona-monitoring-and-management
- search:pmm
- tag:pmm:2167
categories:
- Monitoring
- Percona Software
category_slugs:
- monitoring
- percona-software
tags:
- containers
- PMM
- podman
tag_slugs:
- containers
- pmm
- podman
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/PMM-server-and-podman.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# PMM Server + podman: Running a Container Without root Privileges

Source: [Percona Blog](https://www.percona.com/blog/pmm-server-podman-running-a-container-without-root-privileges/)

Auteur source: [Ceri Williams](https://www.percona.com/blog/author/ceri-williams/)

Publication: 2019-10-22T14:43:07

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this article, we will take a look at how to run Percona Monitoring and Management (PMM) Server in a container without root privileges. Some of the concerns companies have about using Docker relate to the security risks that exist due to the requirement for root privileges in order to run the service and therefore … Continued

## Structure detectee

- H2: Configuring user namespaces
- H2: Look Ma, PMM — and no root!
- H3: Checking the processes
- H2: Using persistent volumes for your data
- H2: Summary

## Images et graphiques reperes

- featured / image: [PMM Server + podman: Running a Container Without root Privileges](https://www.percona.com/wp-content/uploads/2026/03/PMM-server-and-podman.jpg)
- content / image: [PMM server and podman](https://www.percona.com/wp-content/uploads/2026/03/PMM-server-and-podman-300x168.jpg)

## Auteur source

Ceri is a Senior Technical Operations Engineer at Percona. He has previously worked in a variety of industries ranging from telecoms to skin care and online travel, nearly always with a database by his side for more than 10 years. Living in the Welsh Marches area of the UK, Ceri enjoys the rural life and beautiful countryside whenever possible.
