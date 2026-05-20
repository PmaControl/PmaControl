---
title: Percona Operators Custom Resource Monitoring With Kube-state-metrics
source:
  name: Percona Blog
  url: https://www.percona.com/blog/percona-operators-custom-resource-monitoring-with-kube-state-metrics/
  post_id: 27637
source_author:
  name: Chetan Shivashankar
  slug: chetan-shivashankar
  url: https://www.percona.com/blog/author/chetan-shivashankar/
  website: ''
published_at: '2023-11-06T14:11:56'
published_at_gmt: '2023-11-06T14:11:56'
modified_at: '2026-05-05T16:46:22'
modified_at_gmt: '2026-05-05T16:46:22'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- PMM
matched_filters:
- category:monitoring:2104
categories:
- Cloud
- Monitoring
- Percona Software
category_slugs:
- cloud
- monitoring
- percona-software
tags:
- kube-state-metrics
- Kubernetes
- observability
- operators
- Prometheus
tag_slugs:
- kube-state-metrics
- kubernetes
- observability
- operators
- prometheus
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Percona-Operators-Custom-Resource-Monitoring-With-Kube-state-metrics.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Percona Operators Custom Resource Monitoring With Kube-state-metrics

Source: [Percona Blog](https://www.percona.com/blog/percona-operators-custom-resource-monitoring-with-kube-state-metrics/)

Auteur source: [Chetan Shivashankar](https://www.percona.com/blog/author/chetan-shivashankar/)

Publication: 2023-11-06T14:11:56

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

There are more than 300 Operators in operatorhub, and the number is growing. Percona Operators allow users to easily manage complex database systems in a Kubernetes environment. With Percona Operators, users can easily deploy, monitor, and manage databases orchestrated by Kubernetes, making it easier and more efficient to run databases at scale. Our Operators come … Continued

## Structure detectee

- H2: The problem
- H2: The solution
- H2: Details
- H3: Install Kube-state-metrics
- H3: Identify the metrics you want to expose along with the path
- H3: Decide the type of metrics for the fields identified
- H3: Derive the configuration to capture custom resource metrics
- H3: Consume the configuration in kube-state-metrics deployment
- H3: Provide permission to access the custom resources
- H3: Validate the metrics being captured
- H3: Customize the metric name, add default labels
- H3: Labels customization
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Percona Operators Custom Resource Monitoring With Kube-state-metrics](https://www.percona.com/wp-content/uploads/2026/03/Percona-Operators-Custom-Resource-Monitoring-With-Kube-state-metrics.png)
- content / image: [kube-state-metrics](https://www.percona.com/wp-content/uploads/2026/03/blog_ksm_0.png)

## Auteur source

Chetan is passionate about Kubernetes and well versed with Infrastructure and Devops Philosophy. He is playing a key role on running Database on Kubernetes at Percona.
