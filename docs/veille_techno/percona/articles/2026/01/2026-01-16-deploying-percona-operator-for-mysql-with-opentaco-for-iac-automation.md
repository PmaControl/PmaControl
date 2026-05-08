---
title: Deploying Percona Operator for MySQL with OpenTaco for IaC Automation
source:
  name: Percona Blog
  url: https://www.percona.com/blog/deploying-percona-operator-for-mysql-with-opentaco-for-iac-automation/
  post_id: 35578
source_author:
  name: Edith Puclla
  slug: edith-puclla
  url: https://www.percona.com/blog/author/edith-puclla/
  website: ''
published_at: '2026-01-16T14:19:48'
published_at_gmt: '2026-01-16T14:19:48'
modified_at: '2026-03-26T20:25:05'
modified_at_gmt: '2026-03-26T20:25:05'
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
- Cloud
- MySQL
- Percona Software
category_slugs:
- cloud
- mysql
- percona-software
tags:
- cloud
- Kubernetes
- MySQL
- Percona Operator for MySQL
tag_slugs:
- cloud
- kubernetes
- mysql
- percona-operator-for-mysql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Deploying-Percona-Operator-for-MySQL-with-OpenTaco-for-IaC-Automation.jpg
image_count: 4
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Deploying Percona Operator for MySQL with OpenTaco for IaC Automation

Source: [Percona Blog](https://www.percona.com/blog/deploying-percona-operator-for-mysql-with-opentaco-for-iac-automation/)

Auteur source: [Edith Puclla](https://www.percona.com/blog/author/edith-puclla/)

Publication: 2026-01-16T14:19:48

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Deploying databases on Kubernetes is getting easier every year. The part that still hurts is making deployments repeatable and predictable across clusters and environments, especially from Continuous Integration(CI) perspective. This is where PR-based automation helps; you can review a plan, validate changes, and only apply after approval, before anything touches your cluster. If you’ve ever … Continued

## Structure detectee

- H2: What OpenTaco adds to OpenTofu
- H2: Prerequisites
- H2: Demo repository structure
- H2: Run it locally with OpenTofu (Based on Helm)
- H2: Verify that the operator and cluster are running
- H2: Quick connectivity test
- H2: Clean Up (Destroy Everything)
- H1: Time for OpenTaco: PR-based flow
- H3: 1. Install the GitHub App:
- H3: 2. How OpenTaco knows what to run: digger.yml
- H3: 3. Register Actions Secrets
- H3: 4. Testing PR-based workflow with OpenTaco
- H3: 5. OpenTaco UI (otaco.app): what it’s for
- H3: 6. Confirming it worked (CI and state)
- H3: 7. Clean up
- H2: Closing

## Images et graphiques reperes

- featured / image: [Deploying Percona Operator for MySQL with OpenTaco for IaC Automation](https://www.percona.com/wp-content/uploads/2026/03/Deploying-Percona-Operator-for-MySQL-with-OpenTaco-for-IaC-Automation.jpg)
- content / image: [intro-3-1024x396.png](https://www.percona.com/wp-content/uploads/2026/03/intro-3-1024x396.png)
- content / image: [taco-01-1-scaled.png](https://www.percona.com/wp-content/uploads/2026/03/taco-01-1-scaled.png)
- content / image: [taco-02-scaled.png](https://www.percona.com/wp-content/uploads/2026/03/taco-02-scaled.png)

## Auteur source

Edith Puclla is a Technology Evangelist at Percona Corporation, a CNCF Ambassador, an open source contributor with a background in DevOps, and a Docker and Kubernetes enthusiast.
