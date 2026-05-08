---
title: Reducing PostgreSQL Costs in the Cloud
source:
  name: Percona Blog
  url: https://www.percona.com/blog/reducing-postgresql-costs-in-the-cloud/
  post_id: 26613
source_author:
  name: Dev Montiontactic
  slug: mt_admin
  url: https://www.percona.com/blog/author/mt_admin/
  website: ''
published_at: '2025-06-24T13:02:17'
published_at_gmt: '2025-06-24T13:02:17'
modified_at: '2026-05-05T22:57:53'
modified_at_gmt: '2026-05-05T22:57:53'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- PMM
matched_filters:
- search:percona-monitoring-and-management
- search:pmm
categories:
- Insight for DBAs
- PostgreSQL
category_slugs:
- insight-for-dbas
- postgresql
tags:
- Mario PP
- PostgreSQL
tag_slugs:
- mario-planetpostgresql
- postgresql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/lucas.speyer_an_elephant_tusk_made_of_binary_code_grey_colors_9359090d-3dad-43f0-a553-43a95f9e5932.png
image_count: 7
graph_or_chart_count: 5
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Reducing PostgreSQL Costs in the Cloud

Source: [Percona Blog](https://www.percona.com/blog/reducing-postgresql-costs-in-the-cloud/)

Auteur source: [Dev Montiontactic](https://www.percona.com/blog/author/mt_admin/)

Publication: 2025-06-24T13:02:17

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

This post was originally published in March 2023 and was updated in June 2025. If you’re using PostgreSQL in the cloud, there’s a good chance you’re spending more than you need in order to get the results required for your business. Effectively managing PostgreSQL costs in the cloud is crucial, and this post explores practical … Continued

## Structure detectee

- H2: Identify over-provisioning to reduce PostgreSQL cloud costs
- H2: Right-sizing instances to cut PostgreSQL cloud expenses
- H2: Optimizing specific cloud resources for PostgreSQL savings
- H3: Choosing cost-effective cloud CPUs (like AWS Graviton)
- H3: Selecting optimal cloud storage (AWS EBS examples)
- H3: Multi-AZ vs. read replicas: Balancing PostgreSQL cost and high availability
- H4: Multi-AZ deployment
- H4: Read replica
- H4: Which option is better for costs?
- H3: Managing PostgreSQL vacuum processes for cost efficiency
- H3: Serverless PostgreSQL: Understanding the Total Cost of Ownership (TCO)
- H2: Conclusion: Proactive management reduces PostgreSQL cloud costs

## Images et graphiques reperes

- featured / image: [Reducing PostgreSQL Costs in the Cloud](https://www.percona.com/wp-content/uploads/2026/03/lucas.speyer_an_elephant_tusk_made_of_binary_code_grey_colors_9359090d-3dad-43f0-a553-43a95f9e5932.png)
- content / graph_or_chart: [Percona Monitoring and Management (PMM) dashboard showing low CPU usage metric, highlighting potential server over-provisioning for PostgreSQL cost savings.](https://www.percona.com/wp-content/uploads/2026/03/image4-16-1024x471.png)
  Caption: Figure 1: PMM Home Dashboard
- content / graph_or_chart: [Diagram showing Amazon Elastic Block Store (EBS) as a high-performance block storage service suitable for PostgreSQL databases in the cloud.](https://www.percona.com/wp-content/uploads/2026/03/Product-Page-Diagram_Amazon-Elastic-Block-Store.5821c6ee4297f3c01cba37e304922451c828fb04.png)
- content / graph_or_chart: [Diagram illustrating an Amazon RDS Multi-AZ deployment for PostgreSQL high availability.](https://www.percona.com/wp-content/uploads/2026/03/product-page-diagram_MAZ_HIW@2xa.245de181144d709479981ab02a5318165b7ed8a9.png)
- content / graph_or_chart: [Diagram showing Amazon RDS Read Replicas for scaling read traffic in PostgreSQL.](https://www.percona.com/wp-content/uploads/2026/03/read-replicas-scaling-disaster-recovery.3b8da7093daeb1e87426225caf49e32efe7ae01a.png)
  Caption: Figure 4: Amazon RDS Read Replicas
- content / graph_or_chart: [Screenshot of the Experimental PostgreSQL Vacuum Monitoring dashboard in PMM, useful for tracking database bloat.](https://www.percona.com/wp-content/uploads/2026/03/image5-13-1024x505.png)
  Caption: Figure 5: Experimental PostgreSQL Vacuum Monitoring
- content / image: [Enterprise PostgreSQL](https://www.percona.com/wp-content/uploads/2026/03/Get-Enterprise-Postgres.png)
