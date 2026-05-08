---
title: Deploying Any Version of Leading Open Source Databases for Tests and Experiments
source:
  name: Percona Blog
  url: https://www.percona.com/blog/deploying-any-version-of-leading-open-source-databases-for-tests-and-experiments/
  post_id: 23819
source_author:
  name: Nickolay Ihalainen
  slug: nickolay-ihalainen
  url: https://www.percona.com/blog/author/nickolay-ihalainen/
  website: ''
published_at: '2021-01-27T13:57:00'
published_at_gmt: '2021-01-27T13:57:00'
modified_at: '2026-03-26T20:16:14'
modified_at_gmt: '2026-03-26T20:16:14'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MariaDB
- MySQL
- PMM
- ProxySQL
matched_filters:
- category:mariadb:1281
- category:mysql:83
- search:percona-monitoring-and-management
- search:pmm
- search:proxysql
categories:
- Benchmarks
- MariaDB
- MongoDB
- MySQL
- PostgreSQL
category_slugs:
- benchmarks
- mariadb
- mongodb
- mysql
- postgresql
tags:
- insight for DBAs
- Kubernetes
- MariaDB
- MongoDB
- MySQL
- Open Source
- PostgreSQL
tag_slugs:
- insight-for-dbas
- kubernetes
- mariadb
- mongodb
- mysql
- open-source
- postgresql
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Deploying-Any-Version-of-Leading-Open-Source-Databases.png
image_count: 2
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Deploying Any Version of Leading Open Source Databases for Tests and Experiments

Source: [Percona Blog](https://www.percona.com/blog/deploying-any-version-of-leading-open-source-databases-for-tests-and-experiments/)

Auteur source: [Nickolay Ihalainen](https://www.percona.com/blog/author/nickolay-ihalainen/)

Publication: 2021-01-27T13:57:00

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

I want to present a tool for running a specific version of open source databases in a single instance, replication setups, and Kubernetes. AnyDbVer deploys MySQL/MariaDB/MongoDB/PostgreSQL for testing and experiments. It Could Be Started By… Docker (or Podman) or dbdeployer (MySQL-Sandbox successor) could also start a specific database version, but such installations are significantly different … Continued

## Structure detectee

- H3: It Could Be Started By…
- H3: Ansible Playbook
- H3: Bash Scripts
- H3: In LXD containers
- H4: Best Performance with Linux Running on Hardware Directly
- H4: You Can Run Vagrant + VirtualBox as Well, For Other OS
- H2: Single Instance Usage
- H2: Multiple Instances
- H3: Hostnames
- H3: Replication
- H3: MongoDB Sharding
- H2: Containers and Orchestration
- H3: Run Percona Monitoring and Management Docker containers
- H3: Run multi-node Kubernetes cluster
- H4: Percona Kubernetes Operator for Percona XtraDB Cluster
- H4: Percona Kubernetes Operator for Percona Server for MongoDB
- H4: Zalando Postgres Operator
- H2: Summary

## Images et graphiques reperes

- featured / image: [Deploying Any Version of Leading Open Source Databases for Tests and Experiments](https://www.percona.com/wp-content/uploads/2026/03/Deploying-Any-Version-of-Leading-Open-Source-Databases.png)
- content / image: [Deploying Any Version of Leading Open Source Databases](https://www.percona.com/wp-content/uploads/2026/03/Deploying-Any-Version-of-Leading-Open-Source-Databases-300x157.png)

## Auteur source

Nickolay joined Percona in December 2010, after working for several years at what is now the most popular cinema site in Russia. During the time he was there, Nickolay and a small team of developers were responsible for scaling the site into one which now serves over a million unique visitors per day. Prior to that, he worked for several other companies, including NetUp, which provides ISP billing and IPTV solutions, and eHouse, the oldest Russian e-commerce company. Nickolay has a great deal of experience in both systems administration and programming. His experience includes extensive hands-on work with a broad range of technologies, including SQL, MySQL, PHP, C, C++, Python, Java, XML, OS parameter tuning (Linux, Solaris), caching techniques (e.g., memcached), RAID, file systems, SMTP, POP3, Apache, networking and network data formats, and many others. He is an expert in scalability, performance, and system reliability.
