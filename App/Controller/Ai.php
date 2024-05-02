<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use \Glial\Synapse\Controller;
use \App\Library\Extraction;
use \Glial\Sgbd\Sgbd;
use \App\Library\Debug;
use App\Library\Extraction2;

class Ai extends Controller
{


    public function index($param)
    {
        
    }
/****
 * 
 * https://www.blazesql.com/
 * https://zzzcode.ai/sql/query-generator
 * https://aiquery.co/sql-to-english-translator
 * https://www.askyourdatabase.com/
 * https://insightbase.ai/blog/ai-to-sql-tools
 * https://vanna.ai/account/login?next=/account/profile
 * https://www.logicloop.com/ai-sql-query-generator
 * https://medium.com/learning-sql/generative-ai-with-sql-first-impressions-3d26c5f17ae3
 * https://workik.com/ai-powered-sql-query-generator
 * https://www.spaceandtime.io/ai-sql
 * https://www.stork.ai/ai-tools/ai-sql-query-generator
 * https://www.sqlchat.ai/
 * https://www.codecat.ai/ai-sql-query-generator
 * https://www.ai2sql.io/
 * 
 * 
 * https://defog.ai/sqlcoder-demo/
 * https://github.com/defog-ai/sqlcoder  <= to implement
 * 
 * https://www.text2sql.ai/
 * 
 * 
 * https://platform.openai.com/examples/default-sql-translate
 * from openai import OpenAI
client = OpenAI()

response = client.chat.completions.create(
  model="gpt-4",
  messages=[
    {
      "role": "system",
      "content": "Given the following SQL tables, your job is to write queries given a user’s request.\n    \n    CREATE TABLE Orders (\n      OrderID int,\n      CustomerID int,\n      OrderDate datetime,\n      OrderTime varchar(8),\n      PRIMARY KEY (OrderID)\n    );\n    \n    CREATE TABLE OrderDetails (\n      OrderDetailID int,\n      OrderID int,\n      ProductID int,\n      Quantity int,\n      PRIMARY KEY (OrderDetailID)\n    );\n    \n    CREATE TABLE Products (\n      ProductID int,\n      ProductName varchar(50),\n      Category varchar(50),\n      UnitPrice decimal(10, 2),\n      Stock int,\n      PRIMARY KEY (ProductID)\n    );\n    \n    CREATE TABLE Customers (\n      CustomerID int,\n      FirstName varchar(50),\n      LastName varchar(50),\n      Email varchar(100),\n      Phone varchar(20),\n      PRIMARY KEY (CustomerID)\n    );"
    },
    {
      "role": "user",
      "content": "Write a SQL query which computes the average total order value for all orders on 2023-04-01."
    }
  ],
  temperature=0.7,
  max_tokens=64,
  top_p=1
)
 */



}