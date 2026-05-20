---
title: Store and Manage Logs of Percona Operator Pods with PMM and Grafana Loki
source:
  name: Percona Blog
  url: https://www.percona.com/blog/store-and-manage-logs-of-percona-operator-pods-with-pmm-and-grafana-loki/
  post_id: 44137
source_author:
  name: Nickolay Ihalainen
  slug: nickolay-ihalainen
  url: https://www.percona.com/blog/author/nickolay-ihalainen/
  website: ''
published_at: '2022-05-24T18:05:39'
published_at_gmt: '2022-05-24T18:05:39'
modified_at: '2026-04-24T18:20:37'
modified_at_gmt: '2026-04-24T18:20:37'
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
- search:percona-monitoring-and-management
- search:pmm
categories:
- Cloud
- MongoDB
- Monitoring
- MySQL
category_slugs:
- cloud
- mongodb
- monitoring
- mysql
tags: []
tag_slugs: []
featured_image_url: https://www.percona.com/wp-content/uploads/2026/04/Grafana-loki-general-architecture-1024x489-1.png
image_count: 8
graph_or_chart_count: 1
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Store and Manage Logs of Percona Operator Pods with PMM and Grafana Loki

Source: [Percona Blog](https://www.percona.com/blog/store-and-manage-logs-of-percona-operator-pods-with-pmm-and-grafana-loki/)

Auteur source: [Nickolay Ihalainen](https://www.percona.com/blog/author/nickolay-ihalainen/)

Publication: 2022-05-24T18:05:39

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

While it is convenient to view the log of MySQL or MongoDB pods with kubectl logs , sometimes the log is purged when the pod is deleted, which makes searching historical logs a bit difficult. Grafana Loki, an aggregation logging tool from Grafana, can be installed in the existing Kubernetes environment to help store historical logs and … Continued

## Structure detectee

- H3: What Is Grafana Loki
- H3: Installing Grafana Loki in Kubernetes Environment
- H3: Integrating Loki With PMM Grafana
- H3: Exploring the Logs in PMM Grafana
- H3: Conclusion

## Images et graphiques reperes

- content / image: [Grafana Loki](https://www.percona.com/wp-content/uploads/2026/04/Grafana-loki-general-architecture-1024x489-1.png)
- content / image: [Integrating Loki With PMM Grafana](https://www.percona.com/wp-content/uploads/2026/04/configuration-1024x154-1.png)
- content / image: [add-data-source-1024x608-1.png](https://www.percona.com/wp-content/uploads/2026/04/add-data-source-1024x608-1.png)
- content / image: [Loki settings](https://www.percona.com/wp-content/uploads/2026/04/loki-url-1024x221-1.png)
- content / graph_or_chart: [Exploring the Logs in PMM Grafana](https://www.percona.com/wp-content/uploads/2026/04/explore-metric.png)
- content / image: [log-selector.png](https://www.percona.com/wp-content/uploads/2026/04/log-selector.png)
- content / image: [log-screen-1024x480-1.png](https://www.percona.com/wp-content/uploads/2026/04/log-screen-1024x480-1.png)
- content / image: [log-filter-1024x330-1.png](https://www.percona.com/wp-content/uploads/2026/04/log-filter-1024x330-1.png)

## Auteur source

Nickolay joined Percona in December 2010, after working for several years at what is now the most popular cinema site in Russia. During the time he was there, Nickolay and a small team of developers were responsible for scaling the site into one which now serves over a million unique visitors per day. Prior to that, he worked for several other companies, including NetUp, which provides ISP billing and IPTV solutions, and eHouse, the oldest Russian e-commerce company. Nickolay has a great deal of experience in both systems administration and programming. His experience includes extensive hands-on work with a broad range of technologies, including SQL, MySQL, PHP, C, C++, Python, Java, XML, OS parameter tuning (Linux, Solaris), caching techniques (e.g., memcached), RAID, file systems, SMTP, POP3, Apache, networking and network data formats, and many others. He is an expert in scalability, performance, and system reliability.
