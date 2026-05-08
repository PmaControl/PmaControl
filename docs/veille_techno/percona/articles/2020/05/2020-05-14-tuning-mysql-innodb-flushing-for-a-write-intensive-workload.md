---
title: Tuning MySQL/InnoDB Flushing for a Write-Intensive Workload
source:
  name: Percona Blog
  url: https://www.percona.com/blog/tuning-mysql-innodb-flushing-for-a-write-intensive-workload/
  post_id: 22358
source_author:
  name: Yves Trudeau
  slug: yves
  url: https://www.percona.com/blog/author/yves/
  website: http://www.percona.com/
published_at: '2020-05-14T18:57:01'
published_at_gmt: '2020-05-14T18:57:01'
modified_at: '2026-05-04T21:06:40'
modified_at_gmt: '2026-05-04T21:06:40'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- PMM
matched_filters:
- category:mysql:83
- search:percona-monitoring-and-management
- search:pmm
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- InnoDB
- insight for DBAs
- MySQL
- mysql-and-variants
tag_slugs:
- innodb
- insight-for-dbas
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/MySQL-InnoDB-Flushing.png
image_count: 4
graph_or_chart_count: 1
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Tuning MySQL/InnoDB Flushing for a Write-Intensive Workload

Source: [Percona Blog](https://www.percona.com/blog/tuning-mysql-innodb-flushing-for-a-write-intensive-workload/)

Auteur source: [Yves Trudeau](https://www.percona.com/blog/author/yves/)

Publication: 2020-05-14T18:57:01

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

In this post, the third in a series explaining the internals of InnoDB flushing, we’ll focus on tuning. (Others in the series can be seen at InnoDB Flushing in Action for Percona Server for MySQL and Give Love to Your SSDs – Reduce innodb_io_capacity_max!) Understanding the tuning process is very important since we don’t want to … Continued

## Structure detectee

- H2: MySQL Community Before 8.0.19
- H3: innodb_io_capacity
- H3: innodb_io_capacity_max
- H3: innodb_max_dirty_pages_pct
- H3: innodb_max_dirty_pages_pct_lwm
- H3: innodb_page_cleaners
- H3: innodb_purge_threads
- H3: innodb_read_io_threads
- H3: innodb_write_io_threads
- H3: innodb_lru_scan_depth
- H3: innodb_flush_sync
- H3: innodb_log_file_size and innodb_log_files_in_group
- H3: innodb_adaptive_flushing_lwm
- H3: innodb_flushing_avg_loops
- H2: MySQL Community After 8.0.19
- H3: innodb_io_capacity
- H3: innodb_idle_flush_pct
- H2: Percona Server for MySQL 5.7.x and 8.0.x
- H3: innodb_cleaner_lsn_age_factor
- H3: innodb_empty_free_list_algorithm
- H2: Conclusion

## Images et graphiques reperes

- featured / image: [Tuning MySQL/InnoDB Flushing for a Write-Intensive Workload](https://www.percona.com/wp-content/uploads/2026/03/MySQL-InnoDB-Flushing.png)
- content / image: [MySQL InnoDB Flushing](https://www.percona.com/wp-content/uploads/2026/03/MySQL-InnoDB-Flushing-300x168.png)
- content / image: [InnoDB checkpoint age](https://www.percona.com/wp-content/uploads/2026/03/cpa.png)
  Caption: InnoDB checkpoint age
- content / graph_or_chart: [PMM InnoDB IO graph](https://www.percona.com/wp-content/uploads/2026/03/io.png)
  Caption: PMM InnoDB IO graph

## Auteur source

Yves is a Principal Architect at Percona, specializing in distributed technologies such as MySQL Cluster, Pacemaker and XtraDB cluster. He was previously a senior consultant for MySQL and Sun Microsystems. He holds a Ph.D. in Experimental Physics.
