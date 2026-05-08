---
title: Migration of a MySQL Database to a Kubernetes Cluster Using Asynchronous Replication
source:
  name: Percona Blog
  url: https://www.percona.com/blog/migration-of-a-mysql-database-to-a-kubernetes-cluster-using-asynchronous-replication/
  post_id: 24852
source_author:
  name: Slava Sarzhan
  slug: slava-sarzhan
  url: https://www.percona.com/blog/author/slava-sarzhan/
  website: ''
published_at: '2021-09-17T13:44:27'
published_at_gmt: '2021-09-17T13:44:27'
modified_at: '2026-04-28T15:00:17'
modified_at_gmt: '2026-04-28T15:00:17'
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
- Kubernetes Operator
- MySQL
- mysql-and-variants
tag_slugs:
- cloud
- kubernetes
- kubernetes-operator
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Migration-of-a-MySQL-Database-to-a-Kubernetes-Cluster-Using-Asynchronous-Replication.png
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Migration of a MySQL Database to a Kubernetes Cluster Using Asynchronous Replication

Source: [Percona Blog](https://www.percona.com/blog/migration-of-a-mysql-database-to-a-kubernetes-cluster-using-asynchronous-replication/)

Auteur source: [Slava Sarzhan](https://www.percona.com/blog/author/slava-sarzhan/)

Publication: 2021-09-17T13:44:27

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

Nowadays, more and more companies are thinking about the migration of their infrastructure to Kubernetes. Databases are no exception. There were a lot of k8s operators that were created to simplify the different types of deployments and also perform routine day-to-day tasks like making the backups, renewing certificates, and so on. If a few years … Continued

## Structure detectee

- H2: The Goal
- H2: Migration
- H3: Configure the target PXC cluster managed by k8s operator:
- H3: Configure the Source MySQL Server
- H2: Configure the Asynchronous Replication to the Target PXC Cluster
- H2: Verify the Replication
- H2: Promote the PXC Cluster as a Primary
- H3: Conclusion

## Images et graphiques reperes

- featured / image: [Migration of a MySQL Database to a Kubernetes Cluster Using Asynchronous Replication](https://www.percona.com/wp-content/uploads/2026/03/Migration-of-a-MySQL-Database-to-a-Kubernetes-Cluster-Using-Asynchronous-Replication.png)
- content / image: [Migration of a MySQL Database to a Kubernetes Cluster Using Asynchronous Replication](https://www.percona.com/wp-content/uploads/2026/03/Migration-of-a-MySQL-Database-to-a-Kubernetes-Cluster-Using-Asynchronous-Replication-300x157.png)
- content / image: [Migration of MySQL database to Kubernetes cluster using asynchronous replication](https://www.percona.com/wp-content/uploads/2026/03/MySQL-async-replication-k8s-scaled.png)

## Auteur source

Head of Cloud Native Engineering from Lviv, Ukraine, with a passion for building smarter, more efficient Kubernetes solutions. I joined Percona in January 2019, starting as a build/release engineer managing Jenkins farms and automating testing and release processes. Soon after, I moved to the cloud team, creating testing infrastructure and developing new features for Kubernetes operators. Over the years, I’ve grown into leadership, guiding distributed engineering teams to deliver scalable, production-ready solutions while fostering a culture of ownership, clarity, and continuous improvement. Kubernetes isn’t just a tool for me, it’s a playground for innovation. I focus on solving complex problems, improving systems, and sharing insights with the community.
