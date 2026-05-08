---
title: 'Diagnosing MySQL Crashes on RHEL with GDB: How to Identify the Database, Table, and Query Involved'
source:
  name: Percona Blog
  url: https://www.percona.com/blog/diagnosing-mysql-crashes-on-rhel-with-gdb-how-to-identify-the-database-table-and-query-involved/
  post_id: 35112
source_author:
  name: Vinicius Grippa
  slug: vinicius-grippa
  url: https://www.percona.com/blog/author/vinicius-grippa/
  website: ''
published_at: '2025-07-22T13:29:23'
published_at_gmt: '2025-07-22T13:29:23'
modified_at: '2026-03-26T20:25:23'
modified_at_gmt: '2026-03-26T20:25:23'
curator:
  name: Sylvain ARBAUDIE
  slug: sylvain-arbaudie
  source_url: https://www.pmacontrol.fr/fr/site/blog_author/sylvain-arbaudie/
matched_topics:
- MySQL
- Percona Toolkit
matched_filters:
- category:mysql:83
- search:percona-toolkit
categories:
- Insight for DBAs
- MySQL
category_slugs:
- insight-for-dbas
- mysql
tags:
- GDB
- MySQL
- mysql-and-variants
tag_slugs:
- gdb
- mysql
- mysql-and-variants
featured_image_url: https://www.percona.com/wp-content/uploads/2026/03/Diagnosing-MySQL-Crashes-on-RHEL-with-GDB.jpg
image_count: 3
graph_or_chart_count: 0
content_import: metadata_excerpt_headings_and_source_image_urls_only
copyright_notice: Percona All Rights Reserved; full article text and local image copies are not reproduced.
---

# Diagnosing MySQL Crashes on RHEL with GDB: How to Identify the Database, Table, and Query Involved

Source: [Percona Blog](https://www.percona.com/blog/diagnosing-mysql-crashes-on-rhel-with-gdb-how-to-identify-the-database-table-and-query-involved/)

Auteur source: [Vinicius Grippa](https://www.percona.com/blog/author/vinicius-grippa/)

Publication: 2025-07-22T13:29:23

Curateur PmaControl: [Sylvain ARBAUDIE](../../../authors/sylvain-arbaudie.md)

## Note de droits

Fiche de veille uniquement: les metadonnees, l'extrait public, les titres de sections detectes et les URLs des visuels sont conserves. Le texte complet et les copies locales des images restent sur la source Percona.

## Extrait public

When troubleshooting a MySQL crash, having only the error log is rarely enough to pinpoint the exact root cause. To truly understand what happened, we need to go deeper—into the memory state of the process at the moment it crashed. That’s where GDB, the GNU Debugger, comes in. GDB lets us inspect a core dump … Continued

## Structure detectee

- H2: Step 0: Get the Build ID from the MySQL error log and OS version
- H3: Why is the Build ID important?
- H3: What else do we need?
- H2: Step 1: Launch a RHEL 9 (UBI9) debug container
- H2: Step 2: Install MySQL debug binaries and GDB
- H3: What each package does:
- H2: Step 3: Confirm Build ID matches the core dump
- H3: Explanation:
- H2: Step 4: Launch GDB with debug info and Debuginfod
- H3: Explanation:
- H2: Step 5: Extract the query and schema from the core dump
- H3: Goal:
- H3: Step-by-step:
- H4: 1. Show the stack trace:
- H3: 2. Dump the THD (Thread Handle) structure
- H2: Final thoughts

## Images et graphiques reperes

- featured / image: [Diagnosing MySQL Crashes on RHEL with GDB: How to Identify the Database, Table, and Query Involved](https://www.percona.com/wp-content/uploads/2026/03/Diagnosing-MySQL-Crashes-on-RHEL-with-GDB.jpg)
- content / image: [MySQL-Vector-Search-Survey.png](https://www.percona.com/wp-content/uploads/2026/03/MySQL-Vector-Search-Survey.png)
- content / image: [mysql-performance-tuning-6.png](https://www.percona.com/wp-content/uploads/2026/03/mysql-performance-tuning-6.png)

## Auteur source

Vinicius Grippa is a Lead Database Engineer at Percona, an Oracle ACE Director, MySQL Rockstar, and co-author of Learning MySQL. With a Bachelor’s degree in Computer Science and 18 years of experience, he specializes in designing databases for mission-critical applications, focusing on MySQL and MongoDB ecosystems. As part of Percona’s Support team, he has assisted customers in resolving complex database challenges across a wide range of scenarios. An active member of the open-source community, he leads the MySQL User Group in Brazil and engages in knowledge sharing through Slack, Meetups, and international conferences, including FOSDEM, Percona Live, and events across Europe, Asia, and the Americas.
