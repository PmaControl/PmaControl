---
title: Setup and Deploy Vitess on Kubernetes (Minikube) for MySQL – Part II of III
source:
  name: Percona Blog
  url: https://www.percona.com/blog/setup-and-deploy-vitess-on-kubernetes-minikube-for-mysql-part-ii-of-iii/
  post_id: 21380
source_author:
  name: Alkin Tezuysal
  slug: alkin-tezuysal
  url: https://www.percona.com/blog/author/alkin-tezuysal/
  website: https://askdbablog.wordpress.com/
published_at: '2020-01-14T15:48:43'
published_at_gmt: '2020-01-14T15:48:43'
modified_at: '2026-04-27T21:27:01'
modified_at_gmt: '2026-04-27T21:27:01'
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
- Insight for DBAs
- MySQL
category_slugs:
- cloud
- insight-for-dbas
- mysql
tags:
- Automating MySQL
- cloud
- Cloud Databases
- Kubernetes
- MySQL
tag_slugs:
- automating-mysql
- cloud
- cloud-databases
- kubernetes
- mysql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/vitess-kubernetes-mysql.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Setup and Deploy Vitess on Kubernetes (Minikube) for MySQL – Part II of III

Source: [Percona Blog](https://www.percona.com/blog/setup-and-deploy-vitess-on-kubernetes-minikube-for-mysql-part-ii-of-iii/)

Auteur source: [Alkin Tezuysal](https://www.percona.com/blog/author/alkin-tezuysal/)

Publication: 2020-01-14T15:48:43

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, I’d like to share some experiences in setting up a Vitess environment for local tests and development on OSX/macOS. As previously, I have presented How To Test and Deploy Kubernetes Operator for MySQL(PXC) in OSX/macOS, this time I will be showing how to Run Vitess on Kubernetes. Since running Kubernetes on … Continued

## Structure detectee

- H2: Installation and Configuration
- H2: Minikube Installation
- H2: Installation of etcd Operator
- H2: Installation of helm
- H2: Installation of Vitess Client
- H2: Configuration of Vitess Cluster
- H2: Credits

## Images et graphiques reperes

- featured / image: [Setup and Deploy Vitess on Kubernetes (Minikube) for MySQL – Part II of III](https://www.percona.com/wp-content/uploads/2026/03/vitess-kubernetes-mysql.png)
- content / image: [vitess kubernetes mysql](https://www.percona.com/wp-content/uploads/2026/03/vitess-kubernetes-mysql-300x168.png)

## Auteur source

Alkin has extensive experience in enterprise relational databases working in various sectors for large corporations. With more than 20 years of industry experience, he has acquired skills for managing large projects from the ground up to production. For the past 10 years, he's been focusing on e-commerce, SaaS and MySQL technologies. He managed and architected database topologies for high volume sites at eBay Intl. He has several years of experience in 24X7 support and operational tasks as well as improving database systems for major companies. He has led MySQL global operations team on Tier 1/2/3 support for MySQL customers. In 2016 he has joined Percona's expert technical management team.
