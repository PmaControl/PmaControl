---
title: Reduce Your Cloud Costs With Percona Kubernetes Operators
source:
  name: Percona Blog
  url: https://www.percona.com/blog/reduce-your-cloud-costs-with-percona-kubernetes-operators/
  post_id: 26659
source_author:
  name: Sergey Pronin
  slug: sergey-pronin
  url: https://www.percona.com/blog/author/sergey-pronin/
  website: ''
published_at: '2023-03-01T14:02:37'
published_at_gmt: '2023-03-01T14:02:37'
modified_at: '2026-03-26T20:30:08'
modified_at_gmt: '2026-03-26T20:30:08'
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
- Percona Software
category_slugs:
- cloud
- mysql
- percona-software
tags:
- cloud
- Kubernetes
- MySQL
- mysql-and-variants
- operators
tag_slugs:
- cloud
- kubernetes
- mysql
- mysql-and-variants
- operators
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/lucas.speyer_an_icon_of_an_electronic_cloud_orange_sunrise_colo_d9e9f2d4-db38-4a73-982c-622370d50ee7.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Reduce Your Cloud Costs With Percona Kubernetes Operators

Source: [Percona Blog](https://www.percona.com/blog/reduce-your-cloud-costs-with-percona-kubernetes-operators/)

Auteur source: [Sergey Pronin](https://www.percona.com/blog/author/sergey-pronin/)

Publication: 2023-03-01T14:02:37

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Public cloud spending is slowing down. Quarter-over-quarter growth is no longer hitting 30% gains for AWS, Google, and Microsoft. This is businesses’ response to tough and uncertain macroeconomic conditions, where organizations scrutinize their public cloud spending to optimize and adjust. In this blog post, we will see how running databases on Kubernetes with Percona Operators can … Continued

## Structure detectee

- H2: Inputs
- H2: Cutting costs
- H3: Move to Kubernetes
- H3: Why Operators?
- H2: Spot instances
- H2: Leverage container density
- H2: Local storage
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [Reduce Your Cloud Costs With Percona Kubernetes Operators](https://www.percona.com/wp-content/uploads/2026/03/lucas.speyer_an_icon_of_an_electronic_cloud_orange_sunrise_colo_d9e9f2d4-db38-4a73-982c-622370d50ee7.png)
- content / image: [Figure 1: Animated example showing the Kubernetes autoscaler terminating an unused EC2 instance](https://lh5.googleusercontent.com/DhXXSsiH_Inx9kCDMqFURsIOmBLOg8EolcXBVEcebPqII31TnGKkzk30vZ95p6ob5BU3BYDggp9CWW2IAV2nHmHgJI0j_6vcmduexeMzOEZbDKO3BNi3w9Bm9BVAVeYS3fJlkhZoT4hXsooETJjZzQ)
  Caption: Figure 1: Animated example showing the Kubernetes autoscaler terminating an unused EC2 instance

## Auteur source

Sergey is a product leader at Percona focusing on delivering robust open-source database and cloud-native solutions. Prior to Percona Sergey led product management and engineering teams in other organizations with a primary focus on products in infrastructure and platforms space.
