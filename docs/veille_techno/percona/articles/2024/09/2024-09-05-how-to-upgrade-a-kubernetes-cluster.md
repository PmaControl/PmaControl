---
title: How to Upgrade a Kubernetes Cluster
source:
  name: Percona Blog
  url: https://www.percona.com/blog/how-to-upgrade-a-kubernetes-cluster/
  post_id: 28968
source_author:
  name: Chetan Shivashankar
  slug: chetan-shivashankar
  url: https://www.percona.com/blog/author/chetan-shivashankar/
  website: ''
published_at: '2024-09-05T13:14:35'
published_at_gmt: '2024-09-05T13:14:35'
modified_at: '2026-05-05T21:32:34'
modified_at_gmt: '2026-05-05T21:32:34'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- PMM
matched_filters:
- search:percona-monitoring-and-management
categories:
- Insight for DBAs
- Insight for Developers
category_slugs:
- insight-for-dbas
- insight-for-developers
tags:
- databases-on-kubernetes
- Kubernetes
tag_slugs:
- databases-on-kubernetes
- kubernetes
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/imgonline-com-ua-dexifxgVT2XJiUePm.jpg
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# How to Upgrade a Kubernetes Cluster

Source: [Percona Blog](https://www.percona.com/blog/how-to-upgrade-a-kubernetes-cluster/)

Auteur source: [Chetan Shivashankar](https://www.percona.com/blog/author/chetan-shivashankar/)

Publication: 2024-09-05T13:14:35

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I still remember upgrading a Kubernetes cluster for the first time. Despite taking great care and following all the documentation, I managed to break some applications. Luckily, the impact was minimal, and the issue was solved quickly. The most interesting part is that the same set of steps worked perfectly in upgrading non-production clusters, but … Continued

## Structure detectee

- H2: Components involved in the upgrade
- H2: Pre-upgrade checks
- H2: Critical cluster applications
- H2: Applications
- H2: Control plane
- H2: Nodes
- H2: Plan for disaster
- H2: Flow
- H2: How to upgrade a Kubernetes cluster, a conclusion:
- H2: FAQs
- H3: 1. What are the main components involved in upgrading a Kubernetes cluster, and why is their order important?
- H3: 2. How can pre-upgrade checks help prevent issues during a Kubernetes cluster upgrade, and what specific areas should be focused on?
- H3: 3. What strategies can be employed for upgrading nodes in a Kubernetes cluster, and what precautions should be taken?
- H3: 4. Why is disaster recovery planning crucial when upgrading a Kubernetes cluster, and what tools can assist in this process?
- H3: 5. What are some best practices for managing API version changes during a Kubernetes upgrade, and how can potential issues be mitigated?

## Images et graphiques reperes

- featured / image: [How to Upgrade a Kubernetes Cluster](https://www.percona.com/wp-content/uploads/2026/03/imgonline-com-ua-dexifxgVT2XJiUePm.jpg)
- content / image: [K8s Upgrade Flow](https://www.percona.com/wp-content/uploads/2026/03/upgrade-1-568x1024.png)

## Auteur source

Chetan is passionate about Kubernetes and well versed with Infrastructure and Devops Philosophy. He is playing a key role on running Database on Kubernetes at Percona.
