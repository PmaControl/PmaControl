---
title: 'Kubernetes Observability: Code Profiling With Flame Graphs'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/kubernetes-observability-code-profiling-with-flame-graphs/
  post_id: 27546
source_author:
  name: Juan Arruti
  slug: juan-arruti
  url: https://www.percona.com/blog/author/juan-arruti/
  website: ''
published_at: '2023-10-12T13:41:50'
published_at_gmt: '2023-10-12T13:41:50'
modified_at: '2026-03-26T20:27:06'
modified_at_gmt: '2026-03-26T20:27:06'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
matched_filters:
- category:monitoring:2104
- category:mysql:83
categories:
- Cloud
- Monitoring
- MySQL
category_slugs:
- cloud
- monitoring
- mysql
tags:
- cloud
- Kubernetes
- MySQL
- mysql-and-variants
tag_slugs:
- cloud
- kubernetes
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/sci-fi-virtual-reality-landscape-cyberpunk-style-3d-render-fantasy-universe-space-cloud-background-1.jpg
image_count: 2
graph_or_chart_count: 1
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Kubernetes Observability: Code Profiling With Flame Graphs

Source: [Percona Blog](https://www.percona.com/blog/kubernetes-observability-code-profiling-with-flame-graphs/)

Auteur source: [Juan Arruti](https://www.percona.com/blog/author/juan-arruti/)

Publication: 2023-10-12T13:41:50

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, we’ll review how to run Linux profilers such as perf and produce flame graphs on Kubernetes environments. Flame graphs are a graphical representation of function calls. It shows which code paths are more busy on the CPU in given samples. They can be generated with any OS profiler that contains stack … Continued

## Structure detectee

- H2: Kubernetes limitations
- H2: Automatically producing flame graphs with kubectl-flame
- H2: Manual flame graphs collection
- H3: How can we determine which process belongs to the target pod?
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Kubernetes Observability: Code Profiling With Flame Graphs](https://www.percona.com/wp-content/uploads/2026/03/sci-fi-virtual-reality-landscape-cyberpunk-style-3d-render-fantasy-universe-space-cloud-background-1.jpg)
- content / graph_or_chart: [flame graph](https://www.percona.com/wp-content/uploads/2026/03/flamegraph-1-1024x430.png)

## Auteur source

Juan Pablo joined Percona in 2016 as a member of Technical Services Team. Before coming to Percona, he worked as DBA in several companies such as IBM, Turner and Oracle.
