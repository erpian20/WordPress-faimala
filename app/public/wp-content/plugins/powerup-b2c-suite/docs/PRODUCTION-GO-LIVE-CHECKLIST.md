# PowerUp B2C 生产上线检查清单（按顺序执行）

## 1. 基础冻结（上线前 24h）
1. 冻结主题与插件版本，不再改动结构性代码。
2. 备份数据库与 `wp-content` 全目录。
3. 在 staging 完成一次全流程下单（游客 + 登录）。

验收标准：
- staging 与 production 插件版本一致。
- 回滚包可用（数据库 + 文件）。

## 2. 支付与结算
1. Stripe 切换 Live Key，验证 webhook 签名。
2. PayPal 切换 Live App，验证 IPN/Webhook。
3. 如启用 Cardinity，确认生产商户号与密钥生效。
4. 验证 checkout currency 与结算币一致。

验收标准：
- 成功支付 1 单，后台订单状态为 processing/completed。
- 失败支付 1 单，状态保持 pending/failed 且无脏状态。

## 3. 税务与合规
1. VAT/IOSS 字段可见且可写入订单。
2. 隐私政策、退款政策、条款页链接存在。
3. 邮件模板中不暴露敏感参数。

验收标准：
- 订单后台可见 `_powerup_vat_number`、`_powerup_ioss_number`。

## 4. 物流与运费
1. PowerUp Carrier Shipping 在目标 Zone 已启用。
2. 检查 4PX/燕文/云途不同配置下的首重/续重计算。
3. 偏远地区附加费在指定国家触发。

验收标准：
- 3 组重量样本计算结果与预期一致。

## 5. ERP 双向同步
1. ERP -> Woo：products、stock-price、shipments 三接口联调。
2. Woo -> ERP：下单与状态变化推送成功。
3. 幂等验证：重复 event_id 不重复处理。
4. 防重放验证：过期 timestamp、重复 nonce 返回 401。

验收标准：
- Dashboard 队列接近 0（或可解释）。
- 订单备注有成功推送记录。

## 6. 性能与缓存
1. 开启页面缓存/CDN 后再次验证动态页面：cart/checkout/my-account 不缓存。
2. 检查核心页面 LCP、CLS、TTFB。
3. Google Fonts 资源可达，首屏无阻塞错误。

验收标准：
- 关键页面加载 < 3s（目标网络条件）。

## 7. SEO 与可索引性
1. robots、sitemap、canonical 配置正确。
2. Hreflang（WPML/Polylang）输出正常。
3. Product Schema 可被抓取。
4. 图片 ALT 自动补全生效。

验收标准：
- Search Console 无阻断级错误。

## 8. 上线切换
1. 切 DNS（低峰时段）。
2. 发布后立即执行 smoke test（见自测 Runbook）。
3. 观察 60 分钟：支付成功率、ERP 队列、PHP 错误日志。

回滚条件（任一满足立即回滚）：
- 支付成功率异常下降。
- ERP 持续失败且队列快速增长。
- 订单状态流转异常（重复扣款、重复发货）。
