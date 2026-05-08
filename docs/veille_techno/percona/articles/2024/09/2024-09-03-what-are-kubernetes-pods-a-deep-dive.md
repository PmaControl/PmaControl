---
title: What are Kubernetes Pods? A Deep Dive
source:
  name: Percona Blog
  url: https://www.percona.com/blog/what-are-kubernetes-pods-a-deep-dive/
  post_id: 28975
source_author:
  name: Chetan Shivashankar
  slug: chetan-shivashankar
  url: https://www.percona.com/blog/author/chetan-shivashankar/
  website: ''
published_at: '2024-09-03T14:15:49'
published_at_gmt: '2024-09-03T14:15:49'
modified_at: '2026-05-05T16:48:18'
modified_at_gmt: '2026-05-05T16:48:18'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- PMM
matched_filters:
- search:percona-monitoring-and-management
categories:
- Database Trends
- Insight for DBAs
category_slugs:
- database-trends
- insight-for-dbas
tags:
- databases-on-kubernetes
- Kuberenetes
tag_slugs:
- databases-on-kubernetes
- kuberenetes
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Percona-Operators-Custom-Resource-Monitoring-With-Kube-state-metrics.png
image_count: 5
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# What are Kubernetes Pods? A Deep Dive

Source: [Percona Blog](https://www.percona.com/blog/what-are-kubernetes-pods-a-deep-dive/)

Auteur source: [Chetan Shivashankar](https://www.percona.com/blog/author/chetan-shivashankar/)

Publication: 2024-09-03T14:15:49

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

You might have driven a car, but have you ever wondered how the engine works? Similarly if you have used Kubernetes and if you are interested to know how a pod works, this blog post is the right place to start with. Pods are the smallest deployable units of computing that you can create and … Continued

## Structure detectee

- H2: What is a pod and how does it work?
- H2: Containers
- H2: Namespaces
- H2: File system
- H2: Cgroups
- H2: A deep dive into pods
- H3: Networking and storage
- H3: Pod security and management
- H2: A deep dive into Kubernetes pods, a conclusion
- H2: FAQs
- H3: 1. How do containers achieve isolation in a Linux environment, and what role do namespaces play in this process?
- H3: 2. What is the significance of cgroups in container resource management, and how do they work in practice?
- H3: 3. How does the file system isolation in containers work, and what are the benefits of using union file systems?
- H3: 4. What is the relationship between pods and containers in Kubernetes, and how does this affect resource allocation and namespace sharing?
- H3: 5. How do user namespaces enhance container security, and what are the implications of their recent introduction in Kubernetes?

## Images et graphiques reperes

- featured / image: [What are Kubernetes Pods? A Deep Dive](https://www.percona.com/wp-content/uploads/2026/03/Percona-Operators-Custom-Resource-Monitoring-With-Kube-state-metrics.png)
- content / image: [K8s Containers Analogy](https://www.percona.com/wp-content/uploads/2026/03/Dinning.png)
- content / image: [Kubernetes containers](https://www.percona.com/wp-content/uploads/2026/03/pods-3.png)
- content / image: [Kubernetes Pods](https://www.percona.com/wp-content/uploads/2026/03/Pod.png)
- content / image: [Kubernetes Pods explained](https://www.percona.com/wp-content/uploads/2026/03/pods-2.png)

## Auteur source

Chetan is passionate about Kubernetes and well versed with Infrastructure and Devops Philosophy. He is playing a key role on running Database on Kubernetes at Percona.
