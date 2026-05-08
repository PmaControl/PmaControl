---
title: Setup and Deploy Vitess on Kubernetes (Minikube) for MySQL – Part III of III
source:
  name: Percona Blog
  url: https://www.percona.com/blog/setup-and-deploy-vitess-on-kubernetes-minikube-for-mysql-part-iii-of-iii/
  post_id: 21387
source_author:
  name: Alkin Tezuysal
  slug: alkin-tezuysal
  url: https://www.percona.com/blog/author/alkin-tezuysal/
  website: https://askdbablog.wordpress.com/
published_at: '2020-01-15T14:20:26'
published_at_gmt: '2020-01-15T14:20:26'
modified_at: '2026-05-05T16:23:31'
modified_at_gmt: '2026-05-05T16:23:31'
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
- cloud
- DBA
- MySQL
tag_slugs:
- cloud
- dba
- mysql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/vitess-kubernetes-mysql-pt3.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Setup and Deploy Vitess on Kubernetes (Minikube) for MySQL – Part III of III

Source: [Percona Blog](https://www.percona.com/blog/setup-and-deploy-vitess-on-kubernetes-minikube-for-mysql-part-iii-of-iii/)

Auteur source: [Alkin Tezuysal](https://www.percona.com/blog/author/alkin-tezuysal/)

Publication: 2020-01-15T14:20:26

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this blog post, we will continue to explore Vitess and test an example database provided in its repository. This is Part III of the previously discussed installation of Vitess on minikube environment, so please make sure to follow those steps to bring the cluster up to the following level. Shell $ kubectl get pods,jobs NAME READY STATUS RESTARTS AGE po/etcd-global-kbbcqlgvp9 1/1 Running 0 43m po/etcd-zone1-lpc5zmdxxn 1/1 Running 0 43m po/my-release-etcd-operator-etcd-backup-operator-6684dd6d8c-pr4n4 1/1 Running 0 1h po/my-release-etcd-operator-etcd-operator-86d94989d6-w9lpx 1/1 Running 0 1h po/my-release-etcd-operator-etcd-restore-operator-c655d757c-9nsnz 1/1 Running 0 1h po/vtctld-757df48d4-c2gp9 1/1 Running 3 43m po/vtgate-zone1-5cb4fcddcb-k2zsn 1/1 Running 3 43m po/zone1-commerce-0-rdonly-0 6/6 Running 0 43m po/zone1-commerce-0-replica-0 6/6 Running 0 43m po/zone1-commerce-0-rep...

## Structure detectee

- H2: Creating a Keyspace
- H2: Vertical Split
- H2: Creating Customer Tablets
- H2: Creating VerticalSplitClone
- H2: Final Cut Over
- H2: Conclusion
- H2: References
- H2: Credits

## Images et graphiques reperes

- featured / image: [Setup and Deploy Vitess on Kubernetes (Minikube) for MySQL – Part III of III](https://www.percona.com/wp-content/uploads/2026/03/vitess-kubernetes-mysql-pt3.png)
- content / image: [vitess kubernetes mysql pt3](https://www.percona.com/wp-content/uploads/2026/03/vitess-kubernetes-mysql-pt3-300x168.png)

## Auteur source

Alkin has extensive experience in enterprise relational databases working in various sectors for large corporations. With more than 20 years of industry experience, he has acquired skills for managing large projects from the ground up to production. For the past 10 years, he's been focusing on e-commerce, SaaS and MySQL technologies. He managed and architected database topologies for high volume sites at eBay Intl. He has several years of experience in 24X7 support and operational tasks as well as improving database systems for major companies. He has led MySQL global operations team on Tier 1/2/3 support for MySQL customers. In 2016 he has joined Percona's expert technical management team.
