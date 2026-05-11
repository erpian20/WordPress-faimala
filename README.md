# WordPress-faimala

一个基于 WordPress 的电商站点，使用 Local by Flywheel 本地开发环境。这是完整站点镜像仓库，包含所有源码、主题、插件和部署配置。

## 项目概述

**站点名称**: powerup (faimala 品牌)  
**WordPress 版本**: 最新版本  
**核心主题**: powerup-industrial（自定义主题）  
**部署类型**: Nginx + PHP-FPM + MySQL  
**维护工具**: openclaw（自动化维护）

## 目录结构

```
app/
├── public/                 # WordPress 站点根目录
│   ├── wp-config.php      # WordPress 配置（本地开发用）
│   ├── wp-admin/          # WordPress 后台
│   ├── wp-content/        # 主题、插件、上传内容
│   │   ├── themes/
│   │   │   ├── powerup-industrial/      # 主自定义主题
│   │   │   ├── powerup-industrial-child/# 子主题
│   │   │   └── twentytwentyfive/        # 默认主题
│   │   ├── plugins/       # 已安装插件
│   │   └── uploads/       # 用户上传文件
│   ├── deployment/        # 部署相关脚本和文档
│   └── robots.txt         # 爬虫规则
└── sql/                   # 数据库相关（.sql 文件被 gitignore）

conf/
├── nginx/                 # Nginx 配置模板
│   ├── nginx.conf.hbs     # 全局配置
│   ├── site.conf.hbs      # 站点配置
│   └── includes/          # 功能性包含文件
├── php/                   # PHP 配置
│   ├── php.ini.hbs
│   └── php-fpm.d/
└── mysql/                 # MySQL 配置
    └── my.cnf.hbs

backups/                   # 站点备份快照
└── [date]/

logs/                      # 运行时日志（gitignore，不上传）
```

## 快速启动

### 前置条件
- Local by Flywheel 已安装
- 项目已导入或在 Local 中打开

### 本地开发
1. 进入项目目录：
   ```bash
   cd /Users/erpian/Local\ Sites/powerup
   ```

2. 启动 Local 站点（通过 Local 应用）

3. 访问站点：
   - 前台: http://powerup.local
   - 后台: http://powerup.local/wp-admin

### 部署前检查
位置: `app/public/deployment/`

运行预检脚本：
```bash
bash deployment/check-site-health.sh
```

这会验证：
- FastCGI 缓存状态
- SEO 基础设置（robots.txt、sitemap）
- 登录速率限制

## 可修改的文件和目录

### ✅ openclaw 可以维护的区域

**主题开发**:
- `app/public/wp-content/themes/powerup-industrial/` — 完整主题代码，包括 PHP、CSS、JS、配置
- `app/public/wp-content/themes/powerup-industrial-child/` — 子主题覆盖

**插件**:
- `app/public/wp-content/plugins/` — 可以添加、更新或移除插件

**内容和配置**:
- WordPress 后台通过 wp-admin 的所有设置（菜单、页面、文章、分类等）
- 媒体库上传文件

**部署脚本**:
- `app/public/deployment/` 下的 shell 脚本可以优化或扩展

### ❌ openclaw 禁止修改的文件

**WordPress 核心**:
- `app/public/wp-admin/` — WordPress 核心后台文件
- `app/public/wp-includes/` — WordPress 核心库
- 所有 WordPress 根文件（index.php、wp-load.php 等）

**数据库配置**:
- `app/public/wp-config.php` — 数据库连接信息，本地与生产环境可能不同

**Local 专用配置**:
- `conf/` 目录下的 *.hbs 配置文件 — 由 Local 管理，不应手动修改

**备份**:
- `backups/` — 历史快照，只用于参考和恢复，不应作为常规修改对象

**系统文件**:
- `.git/` — 版本控制元数据
- `.gitignore` — 忽略规则
- 任何 `.venv/` 或 Python 虚拟环境

## 关键配置说明

### WordPress 配置 (`wp-config.php`)
- 数据库使用 Local 本地 MySQL socket 连接
- DEBUG 模式可根据环境启用/禁用
- 盐值和密钥已生成（production 部署时应重新生成）

### 主题配置 (`powerup-industrial`)
主题配置集中在 `config/` 目录：
- `constants.php` — 主题常量定义
- `settings.php` — 主题选项和自定义设置
- `hooks.php` — 钩子绑定
- `assets.php` — 资源加载

**禁止修改的核心文件**:
- `functions.php` — 主题入口，修改需谨慎
- `style.css` — 主题元信息头部

### 部署配置 (`conf/`)
- Nginx 配置启用了 FastCGI 缓存
- PHP 配置包含 opcache 优化
- 登录速率限制和 XMLRPC 访问限制已配置
- 这些配置在 production 部署时需要根据实际服务器调整

## 维护和更新流程

### 添加新主题或插件
1. 在 WordPress 后台安装（推荐）或在 `wp-content/themes/` 或 `wp-content/plugins/` 下手动添加
2. 激活并测试
3. 提交到 Git：
   ```bash
   git add app/public/wp-content/
   git commit -m "Add [plugin/theme name]"
   git push
   ```

### 修改主题代码
1. 编辑 `app/public/wp-content/themes/powerup-industrial/` 下的文件
2. 在本地浏览器验证
3. 测试无误后提交：
   ```bash
   git add app/public/wp-content/themes/powerup-industrial/
   git commit -m "Update: [具体改动描述]"
   git push
   ```

### 更新 WordPress 或插件
1. 在 Local 或 WordPress 后台执行更新
2. 测试功能完整性
3. 如有必要，提交更新后的版本号变化

## 部署到生产环境

部署前的必读文档：
- `app/public/deployment/CLOUD_LAUNCH_CHECKLIST.md` — 云部署清单
- `app/public/deployment/FREEZE_POLICY_20260419.md` — 冻结政策说明

关键部署文件：
- `app/public/deployment/nginx-powerup.conf` — Production Nginx 配置（包含 FastCGI 缓存、速率限制等）

**重要**: 部署时务必：
1. 更新 `wp-config.php` 中的数据库凭证和 URL
2. 使用强随机盐值替换现有盐值
3. 禁用 WP_DEBUG 或设置 WP_DEBUG_LOG
4. 验证 HTTPS 证书配置
5. 运行 `check-site-health.sh` 验证部署配置

## Git 工作流

### 分支策略
- `main` — 生产就绪分支，包含完整站点镜像
- 其他分支（如 `develop`、`feature/*`）可根据团队需要添加

### 提交规范
```
[功能区域]: 改动描述

# 例子
theme: Update powerup-industrial hero section styling
plugin: Add new e-commerce extension
config: Adjust nginx cache headers for product pages
```

### 推送和同步
```bash
git pull origin main      # 同步最新更改
git status                # 检查未跟踪的改动
git add [files]           # 暂存改动
git commit -m "[msg]"     # 提交
git push origin main      # 推送到远程
```

## 常见问题

### Q: 为什么某些文件在 gitignore 中？
A: `logs/` 和 `app/sql/` 被排除是因为它们包含本地运行时数据或敏感信息（数据库密码等），不应纳入版本控制。

### Q: 我可以修改 conf/ 目录吗？
A: **不建议**。这些是 Local 管理的配置模板。如果需要自定义部署配置，建议在 production 部署时创建对应的文件，而不是修改这里的模板。

### Q: 如何备份当前站点？
A: 使用 Local 的备份功能或手动运行：
```bash
bash app/public/deployment/clear-fastcgi-cache.sh  # 清缓存后再备份
```

### Q: 如何使用 openclaw 维护这个仓库？
A: openclaw 应该遵循以下规则：
- **可改**: 主题代码、插件文件、部署脚本
- **不可改**: WordPress 核心、wp-config.php、conf/ 配置
- 每次改动后推送 Git 提交
- 定期运行 `check-site-health.sh` 验证部署状态

## 联系和支持

- 仓库地址: https://github.com/erpian20/WordPress-faimala
- 维护者: erpian20
- 维护工具: openclaw

---

**最后更新**: 2026-05-11  
**版本**: 1.0 (Initial full-site snapshot)
