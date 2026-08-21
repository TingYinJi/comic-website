### 漫畫網站 (Comic Website) ###

113學年度第一學期資料庫系統期末專題報告

---

### 簡介與動機 (Introduction & Motivation) ###
在數位化時代，許多讀者希望能夠隨時隨地輕鬆閱讀自己喜愛的漫畫，無需受限於實體書籍或繁瑣的下載程序。
本專題旨在設計一個整合各類漫畫作品的線上平台，滿足不同讀者的閱讀需求，並提供漫畫創作者一個展示和推廣作品的舞台，促進漫畫文化的傳播與發展。

---

### 系統架構與功能 (System Architecture) ###
本系統採用前後端分離與模組化概念設計，主要包含以下核心功能：
** 用戶管理：註冊、登入、登出機制。
** 漫畫瀏覽與管理：首頁漫畫展示、作者資訊、標籤分類檢索。
** 互動功能： 漫畫收藏、評分機制、評論區互動與排行榜功能。

---

### 資料庫設計 (Database Design - ER Model & Tables) ###

系統資料庫包含以下核心表格與關聯：
*  用戶表 (User)：記錄 `userID`、`userName`、`Email`、`password`、`Registration Date`
*  漫畫表 (Comic)：記錄 `comicID`、`Title`、`Release Date`、`Author ID`、`Views`
*  作者表 (Author)：記錄 `AuthorID`、`comicID`、`Author Name`
*  評論表 (Comment)：記錄 `commentID`、`comment date`、`content`、`comicID`、`userID`
*  收藏表 (Favorite)：記錄 `favorite ID`、`comic ID`、`userID`、`favorite date`
*  評分表 (Rating)：記錄 `rating ID`、`period`、`category`、`comicID`、`Score`
*  標籤表 (Tag)：記錄 `tagID`、`comicID`、`tagName`

---

### 專案成果展示 ###
詳細的系統設計、ER Model 圖解與實作成果請參考根目錄下的 [漫畫網站.pdf](./漫畫網站.pdf)。
