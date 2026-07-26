# 🧠 AICO Platform — AI Engineering Blueprint
### Lộ trình học & triển khai AI Assistant Module (Software Engineer → AI Engineer)

---

## 0. Mục đích tài liệu

`AI Assistant Module` gốc là một **feature list** đầy đủ (~50 tính năng cho Admin & Client). Tài liệu này biến nó thành một **lộ trình học có thứ tự**, để một người có nền tảng Software Engineering (đã quen Phase 1-3 của AICO: React/Laravel/MySQL) có thể:

1. Học Python & AI Engineering theo từng **Level**, từ dễ đến khó.
2. Ở mỗi Level, chọn **đúng nhóm tính năng AI** trong module để thực hành — vừa học vừa ship sản phẩm thật, không học chay.
3. Biết khi nào nên dừng "học" và bắt đầu "làm cứng" (production-ize) trước khi qua Level tiếp theo.

**Cách dùng:** đọc tuần tự theo Level. Không cần giỏi hết một Level mới code — nên bắt tay vào tính năng ví dụ ngay khi nắm được khái niệm cốt lõi, để có phản hồi nhanh.

---

## 1. Nguyên tắc thiết kế lộ trình

- **Học đi kèm output thật** — mỗi Level đều gắn với tính năng cụ thể trong AI Assistant Module, không có Level "học lý thuyết suông".
- **Đừng học framework trước khi hiểu cơ chế.** Ví dụ: gọi thẳng OpenAI/Anthropic SDK trước, chỉ thêm LangChain/LlamaIndex khi đã hiểu rõ retrieval/tool-calling hoạt động thế nào.
- **Tách AI Service ra khỏi Laravel.** PHP không phải hệ sinh thái mạnh cho AI/ML — nên xây một service Python riêng, giao tiếp với Laravel qua REST nội bộ. Đây cũng là bước tập dượt tốt cho Phase 5 (Distributed Architecture) của roadmap chính.
- **Giữ đúng tinh thần "Copilot, không phải Agent"** xuyên suốt: mọi tính năng ở mọi Level đều có bước xác nhận của người dùng trước khi ghi dữ liệu thật.
- **Ưu tiên một bộ tính năng "flagship" làm sâu**, thay vì dàn trải 50 tính năng làm nông (xem mục 4).

---

## 2. Kiến trúc đề xuất: AI Service tách biệt

```
React (Frontend)
      │
      ▼
Laravel (Core Backend) ── REST nội bộ (API key/service token) ──► ai-service (FastAPI, Python)
      │                                                                  │
      ▼                                                                  ▼
  MySQL (dữ liệu business)                                    LLM Provider + Vector DB
```

`ai-service` là nơi chứa toàn bộ logic ở tài liệu này. Laravel chỉ gọi API và hiển thị kết quả — không nhúng logic AI vào PHP.

---

## 3. Sơ đồ tổng quan các Level

```
Level 0  Chuẩn bị nền tảng
   ↓
Level 1  Python Engineering Foundations
   ↓
Level 2  LLM Prompting & Text Generation
   ↓
Level 3  Structured Output & Tool/Function Calling
   ↓
Level 4  Embeddings & Vector Search
   ↓
Level 5  Retrieval-Augmented Generation (RAG)
   ↓
Level 6  Classification & Lightweight ML
   ↓
Level 7  Recommendation Systems
   ↓
Level 8  Forecasting & Time Series
   ↓
Level 9  Computer Vision / Image AI
   ↓
Level 10 Conversational Copilot (Tool-using Agent)
   ↓
Level 11 Productionization (MLOps-lite)
```

---

## 4. Chi tiết từng Level

> Ký hiệu: **Admin §x.y** / **Client §x** tham chiếu đúng số mục trong AI Assistant Module gốc.

### Level 0 — Chuẩn bị nền tảng
- **Mục tiêu:** Đảm bảo nền Python cơ bản + hiểu REST API (đã quen từ Laravel nên phần này thường nhanh).
- **Kỹ năng:** cú pháp Python, virtual env (venv/poetry), đọc hiểu HTTP request/response.
- **Thời gian:** 3-5 ngày nếu đã có nền lập trình.
- **Output:** Không gắn tính năng — đây là bước rà lại nền tảng.

### Level 1 — Python Engineering Foundations
- **Mục tiêu:** Viết Python theo chuẩn kỹ sư backend, không phải script rời rạc.
- **Kỹ năng:** OOP, type hints, `pytest`, async/await, `httpx`/`requests`, đóng gói một service FastAPI tối thiểu.
- **Thời gian:** 1-2 tuần.
- **Output:** Service `ai-service` khởi tạo với 1 endpoint `GET /health`, containerize bằng Docker, thêm vào `docker-compose` hiện có của AICO.

### Level 2 — LLM Prompting & Text Generation
- **Mục tiêu:** Gọi LLM API ổn định: system prompt, temperature, xử lý lỗi/retry, ước lượng chi phí token.
- **Kỹ năng:** prompt design, few-shot examples, streaming response.
- **Thời gian:** 2 tuần.
- **Tính năng thực hành:** Admin §1.1 Generate Product Description, §1.2 Rewrite Description, §1.3 Generate SEO, §1.4 Generate Product Tags, §1.6 Generate FAQ, §1.7 Multi-language Translation, §3.1–3.7 (Email/SMS/Push/Facebook/TikTok/Blog/Landing Page Generator) — cùng một kỹ thuật, khác template prompt.
- **Output:** Endpoint `POST /ai/product-copy` nhận thông tin sản phẩm, trả về mô tả + SEO + tags + FAQ.

### Level 3 — Structured Output & Tool/Function Calling
- **Mục tiêu:** Ép LLM trả về dữ liệu có schema thay vì text tự do.
- **Kỹ năng:** JSON schema, function/tool calling, Pydantic validation, retry khi output sai schema.
- **Thời gian:** 1-2 tuần.
- **Tính năng thực hành:** Admin §1.5 Generate Product Attributes (schema theo từng category), §1.8 Generate Product Variants, §3.8/§3.9 Campaign/Coupon Suggestion (chọn từ enum có sẵn), §6.1 AI Reply Suggestion (luôn ở trạng thái "draft", nhân viên duyệt mới gửi), §6.4 Refund Suggestion (structured decision, kết hợp RAG ở Level 5 để tra chính sách hoàn tiền trước khi đề xuất), Client §6 Product Comparison (structured output bảng so sánh specs + narrative nêu khác biệt).
- **Output:** Endpoint trả JSON đã validate, không cần regex parse text.

### Level 4 — Embeddings & Vector Search
- **Mục tiêu:** Hiểu embedding là gì, đo độ tương đồng, lưu trữ trong vector DB.
- **Kỹ năng:** embedding model (OpenAI `text-embedding-3-small` hoặc mã nguồn mở như `bge-small`/`e5` qua Hugging Face), cosine similarity, vector DB (Qdrant tự host bằng Docker, hoặc pgvector nếu thêm Postgres).
- **Thời gian:** 2-3 tuần.
- **Tính năng thực hành:** Client §2 Smart Search, §3 Semantic Search, §4 Product Recommendation (Similar Products), §9 FAQ Assistant (match theo embedding trước, fallback LLM nếu không khớp).
- **Output:** Endpoint semantic search trả sản phẩm liên quan theo nghĩa, không cần đúng từ khóa.

### Level 5 — Retrieval-Augmented Generation (RAG)
- **Mục tiêu:** Kết hợp retrieval + generation để trả lời có căn cứ, giảm hallucination.
- **Kỹ năng:** chiến lược chunking, ghép context vào prompt, trích dẫn nguồn, xử lý khi không tìm thấy thông tin (trả lời "không chắc" thay vì bịa).
- **Thời gian:** 2-3 tuần.
- **Tính năng thực hành:** Client §8 Product Q&A (Specs/FAQ/Review/Manual), §7 Review Summary, §15 Return Policy Assistant, Admin §7.1–7.4 Store/Product/Customer/Order Q&A, §7.5 KPI Explanation, §7.6 System Guide (RAG ngay trên thư mục `docs/` sẵn có của AICO — rất hợp lý).
- **Output:** Endpoint RAG có trích dẫn nguồn dữ liệu dùng để trả lời.

### Level 6 — Classification & Lightweight ML
- **Mục tiêu:** Biết khi nào dùng ML cổ điển thay vì gọi LLM (rẻ hơn, nhanh hơn, ổn định hơn cho tác vụ đơn giản).
- **Kỹ năng:** `scikit-learn` cơ bản, hoặc LLM zero-shot classification với structured output (Level 3).
- **Thời gian:** 2 tuần.
- **Tính năng thực hành:** Admin §6.3 Sentiment Analysis, §4.10 Detect Anomaly (thống kê z-score/IQR, không cần ML phức tạp), §4.6 Trend Analysis, §6.2 Ticket Summary, §4.2 Sales Analysis, §4.3 Top Selling Products, §4.4 Worst Selling Products (3 mục này thực chất là SQL aggregation — AI chỉ dùng để diễn giải số liệu bằng ngôn ngữ tự nhiên, không cần train model gì cả), §4.5 Customer Insight (mục duy nhất trong Domain 4 cần ML thật sự — phân khúc khách hàng bằng RFM analysis hoặc K-means clustering).
- **Output:** Endpoint phân loại sentiment + cảnh báo bất thường cơ bản.

### Level 7 — Recommendation Systems
- **Mục tiêu:** Học gợi ý cá nhân hóa vượt ra ngoài embedding similarity — dùng hành vi người dùng thật.
- **Kỹ năng:** ma trận user-item, collaborative filtering, xử lý cold-start (fallback về embedding của Level 4 khi user mới).
- **Thời gian:** 2-3 tuần.
- **Tính năng thực hành:** Client §5 Personalized Recommendation, §10 Cart Recommendation, §17 Personalized Homepage, Admin §3.10 Cross-selling Suggestion, Client §12/§13 Size/Outfit Recommendation (kết hợp rule-based + LLM giải thích), Client §11 Coupon Suggestion (biến thể cá nhân hóa của Admin §3.9), Client §4 Product Recommendation — phần *Frequently Bought Together* (cần thêm association rule mining như Apriori/FP-Growth, khác kỹ thuật với similarity ở Level 4); phần *Trending*/*New Arrival* không cần ML, chỉ là query theo số lượng bán/thời gian tạo.
- **Output:** Endpoint trả danh sách gợi ý theo từng user, khác nhau giữa các user.

### Level 8 — Forecasting & Time Series
- **Mục tiêu:** Dự báo số liệu kinh doanh cơ bản.
- **Kỹ năng:** `pandas` time series, moving average/exponential smoothing trước, chỉ dùng Prophet/ARIMA nếu cần độ chính xác cao hơn.
- **Thời gian:** 1-2 tuần.
- **Tính năng thực hành:** Admin §4.7 Inventory Forecast, §4.8 Revenue Forecast, §5.5 Seasonal Prediction (seasonal decomposition), §5.1/§5.2 Low/Overstock Warning (ngưỡng đơn giản, không cần AI), §5.3 Suggested Restock, §5.4 Suggested Clearance (cả hai là bước phái sinh từ forecast — lấy số dự báo rồi cho LLM diễn giải thành gợi ý hành động cụ thể).
- **Output:** Endpoint dự báo tồn kho/doanh thu theo tuần/tháng.

### Level 9 — Computer Vision / Image AI
- **Mục tiêu:** Tích hợp model ảnh có sẵn, **không tự train** — đúng phạm vi cho một platform thương mại.
- **Kỹ năng:** gọi API xử lý ảnh (remove/replace background, upscale), OCR (Tesseract mã nguồn mở hoặc Cloud Vision API), vision-LLM cho alt text.
- **Thời gian:** 2 tuần.
- **Tính năng thực hành:** Admin §2.1 Remove Background, §2.2 Replace Background, §2.3 Image Enhancement, §2.4 Image Upscale (4 mục này dùng model segmentation/super-resolution có sẵn), §2.5 Generate Alt Text (vision-LLM), §2.6 OCR (Tesseract/Cloud API), §2.7 Generate Lifestyle Image — *tuỳ chọn, khó nhất trong Domain 2*: cần model sinh ảnh (text-to-image/inpainting), kỹ thuật khác hẳn nhóm trên nên để làm sau cùng nếu còn thời gian.
- **Output:** Endpoint xử lý ảnh sản phẩm, sinh alt text tự động.

### Level 10 — Conversational Copilot (Tool-using Agent)
- **Mục tiêu:** Level khó nhất — LLM + tool calling truy vấn dữ liệu thật, có kiểm soát chặt (read-only, tham số hóa, không cho phép hành động ghi dữ liệu).
- **Kỹ năng:** orchestration tool-calling, quản lý session hội thoại, guardrail chống truy vấn ngoài phạm vi cho phép.
- **Thời gian:** 3-4 tuần.
- **Tính năng thực hành:** Admin §4.1 AI Dashboard Chat, §4.9 Explain Dashboard, Client §1 AI Shopping Assistant, §14 Order Assistant, §16 Delivery Assistant.
- **Output:** Chat copilot nội bộ, chỉ đọc dữ liệu, mọi câu trả lời đều log lại để review.

### Level 11 — Productionization (MLOps-lite)
- **Mục tiêu:** Làm cứng toàn bộ AI service trước khi tính là "production-ready" (khớp với Phase 3 của roadmap chính).
- **Kỹ năng:** cache bằng Redis (đã có sẵn trong stack), rate limiting, versioning prompt, log input/output để đánh giá, bộ "golden prompts" test, kiểm soát chi phí gọi API, fallback khi LLM lỗi.
- **Thời gian:** liên tục, song song các Level trên.
- **Output:** Không có tính năng mới — đây là lớp bảo vệ cho toàn bộ hệ thống AI.

---

## 5. Đề xuất "AI MVP Feature Set" (làm sâu thay vì làm rộng)

Thay vì cố cài đặt cả ~50 tính năng, nên chọn 1 tính năng đại diện mỗi kỹ thuật để làm cho thật tốt — đủ để chứng minh năng lực trong CV/portfolio:

| # | Tính năng | Kỹ thuật minh họa | Level |
|---|-----------|-------------------|-------|
| 1 | Admin §1.1 Generate Product Description | Prompting cơ bản | 2 |
| 2 | Admin §1.3 Generate SEO | Structured text gen | 2 |
| 3 | Client §3 Semantic Search | Embeddings | 4 |
| 4 | Client §4 Similar Products | Vector similarity | 4 |
| 5 | Client §8 Product Q&A | RAG | 5 |
| 6 | Client §7 Review Summary | RAG (map-reduce) | 5 |
| 7 | Admin §6.3 Sentiment Analysis | Classification | 6 |
| 8 | Client §5 Personalized Recommendation | RecSys | 7 |
| 9 | Admin §2.5 Generate Alt Text | Vision AI | 9 |
| 10 | Admin §4.1 AI Dashboard Chat | Tool-calling agent (capstone) | 10 |

---

## 6. Tech Stack theo nhóm kỹ năng

| Nhóm | Công cụ đề xuất |
|---|---|
| AI Service | Python 3.11+, FastAPI |
| LLM Provider | OpenAI API hoặc Anthropic API (chọn 1, thiết kế interface chung để dễ đổi) |
| Structured Output | Pydantic + JSON schema / function calling |
| Vector DB | Qdrant (tự host qua Docker) hoặc pgvector nếu thêm Postgres |
| Embeddings | `text-embedding-3-small` (OpenAI) hoặc mã nguồn mở (`bge-small`, `e5`) qua Hugging Face |
| ML cổ điển | scikit-learn |
| Forecasting | pandas + statsmodels (Prophet nếu cần nâng cao) |
| Recommendation | Embedding-based cho MVP, `implicit`/LightFM nếu mở rộng |
| Vision/OCR | Hugging Face inference API hoặc Cloud Vision, Tesseract OCR |
| Orchestration | Raw SDK + tool-calling thủ công trước; LangChain/LlamaIndex chỉ khi thật cần |
| Giao tiếp Laravel ↔ AI Service | REST nội bộ + service token |
| Testing/Eval | pytest + bộ "golden prompts" cho AI output |

---

## 7. Tích hợp vào roadmap chính

- Toàn bộ tài liệu này là bản chi tiết hóa cho **Phase 4 — AI Commerce** trong roadmap gốc. Có thể triển khai song song với Phase 2-3 thay vì chờ tuần tự, vì `ai-service` độc lập với Laravel.
- Đề xuất thêm 1 doc mới vào `docs/`:
  - `10-ai-evaluation-and-safety.md` — guardrail, xử lý dữ liệu khách hàng (PII) khi AI truy vấn thông tin khách VIP/đơn hàng, kiểm soát chi phí gọi LLM API, chiến lược fallback khi model lỗi.
- Level 10 (Conversational Copilot) và Level 11 (Productionization) nên hoàn thành song song với Phase 5 (Distributed Architecture, Observability) vì cùng nhóm mối quan tâm (monitoring, tracing, health checks).

---

## 8. Ước lượng thời gian (part-time, ~10-15 giờ/tuần)

- Level 0-8 (nền tảng đến forecasting): **~5-7 tháng**
- Level 9-11 (Vision, Agent, Production): **~2-3 tháng** thêm
- Có thể rút ngắn đáng kể nếu chỉ tập trung vào bộ "AI MVP Feature Set" ở mục 5 thay vì học sâu toàn bộ mỗi Level.

---

## 9. Phụ lục — Bảng ánh xạ đầy đủ 100% (đúng 50 mục Admin, theo từng Domain)

Không nhóm theo range nữa — liệt kê từng mục một để không sót. Cột "Ghi chú" nói rõ mục nào thật sự cần AI/ML và mục nào chỉ là query/rule (AI chỉ đóng vai trò diễn giải).

### Domain 1 — AI Product Assistant (8/8)
| Mục | Tính năng | Level | Ghi chú |
|---|---|---|---|
| 1.1 | Generate Product Description | 2 | Prompting cơ bản |
| 1.2 | Rewrite Description | 2 | Prompting + style control |
| 1.3 | Generate SEO | 2 | Structured text gen |
| 1.4 | Generate Product Tags | 2 | Text gen → list |
| 1.5 | Generate Product Attributes | 3 | Structured output theo schema/category |
| 1.6 | Generate FAQ | 2 | Few-shot theo category sản phẩm |
| 1.7 | Multi-language Translation | 2 | LLM dịch trực tiếp, không cần model dịch riêng |
| 1.8 | Generate Product Variants | 3 | Structured output (enum options) |

### Domain 2 — AI Image Assistant (7/7)
| Mục | Tính năng | Level | Ghi chú |
|---|---|---|---|
| 2.1 | Remove Background | 9 | Model segmentation có sẵn |
| 2.2 | Replace Background | 9 | Segmentation + composite ảnh |
| 2.3 | Image Enhancement | 9 | Model denoise/enhance có sẵn |
| 2.4 | Image Upscale | 9 | Real-ESRGAN/Cloud API |
| 2.5 | Generate Alt Text | 9 | Vision-LLM |
| 2.6 | OCR | 9 | Tesseract/Cloud OCR |
| 2.7 | Generate Lifestyle Image (tuỳ chọn) | 9+ | Text-to-image/inpainting — khó nhất Domain 2, kỹ thuật khác hẳn 6 mục trên |

### Domain 3 — AI Marketing Assistant (10/10)
| Mục | Tính năng | Level | Ghi chú |
|---|---|---|---|
| 3.1 | Email Generator | 2 | Prompting |
| 3.2 | SMS Generator | 2 | Prompting |
| 3.3 | Push Notification Generator | 2 | Prompting |
| 3.4 | Facebook Content | 2 | Prompting |
| 3.5 | TikTok Caption | 2 | Prompting |
| 3.6 | Blog Generator | 2 | Prompting multi-step (outline → full bài) |
| 3.7 | Landing Page Content | 2 | Structured sections (headline/subhead/CTA) |
| 3.8 | Campaign Suggestion | 3 | Structured output, chọn từ enum |
| 3.9 | Coupon Suggestion | 3 | Structured output + rule ràng buộc (margin, tồn kho) |
| 3.10 | Cross-selling Suggestion | 7 | Association/embedding-based recommendation |

### Domain 4 — AI Business Analytics (10/10)
| Mục | Tính năng | Level | Ghi chú |
|---|---|---|---|
| 4.1 | AI Dashboard Chat | 10 | Tool-calling agent, truy vấn số liệu thật |
| 4.2 | Sales Analysis | 6 | SQL aggregation + LLM diễn giải — không cần ML |
| 4.3 | Top Selling Products | 6 | Tương tự 4.2 |
| 4.4 | Worst Selling Products | 6 | Tương tự 4.2 |
| 4.5 | Customer Insight | 6 | Mục duy nhất trong Domain 4 cần ML thật: RFM analysis / K-means |
| 4.6 | Trend Analysis | 6 | Rolling average + LLM giải thích |
| 4.7 | Inventory Forecast | 8 | Time series forecasting |
| 4.8 | Revenue Forecast | 8 | Time series forecasting |
| 4.9 | Explain Dashboard | 10 | Tool-calling + RAG kết hợp |
| 4.10 | Detect Anomaly | 6 | Thống kê z-score/IQR, không cần ML phức tạp |

### Domain 5 — AI Inventory Assistant (5/5)
| Mục | Tính năng | Level | Ghi chú |
|---|---|---|---|
| 5.1 | Low Stock Warning | 8 | Rule ngưỡng đơn giản, không cần AI |
| 5.2 | Overstock Warning | 8 | Rule ngưỡng đơn giản, không cần AI |
| 5.3 | Suggested Restock | 8 | Phái sinh từ forecast (4.7) + LLM diễn giải |
| 5.4 | Suggested Clearance | 8 | Phái sinh từ overstock + slow-moving detection |
| 5.5 | Seasonal Prediction | 8 | Seasonal decomposition |

### Domain 6 — AI Customer Support Assistant (4/4)
| Mục | Tính năng | Level | Ghi chú |
|---|---|---|---|
| 6.1 | AI Reply Suggestion | 3 | Structured draft, luôn cần người duyệt |
| 6.2 | Ticket Summary | 2/5 | Summarization (map-reduce nếu hội thoại dài) |
| 6.3 | Sentiment Analysis | 6 | Classification |
| 6.4 | Refund Suggestion | 3+5 | Structured decision, cần RAG tra chính sách hoàn tiền trước |

### Domain 7 — AI Store Copilot (6/6)
| Mục | Tính năng | Level | Ghi chú |
|---|---|---|---|
| 7.1 | Store Q&A | 5/10 | RAG hoặc tool-calling tuỳ độ phức tạp câu hỏi |
| 7.2 | Product Q&A (admin) | 5 | RAG trên dữ liệu tồn kho |
| 7.3 | Customer Q&A | 10 | Tool-calling — dữ liệu nhạy cảm, bắt buộc guardrail |
| 7.4 | Order Q&A | 10 | Tool-calling |
| 7.5 | KPI Explanation | 5 | RAG trên tài liệu định nghĩa KPI |
| 7.6 | System Guide | 5 | RAG trên docs/ nội bộ |

**Tổng Admin: 8+7+10+10+5+4+6 = 50/50 ✅**

### Client-side (17/17)
| Mục | Tính năng | Level | Ghi chú |
|---|---|---|---|
| 1 | AI Shopping Assistant | 10 | Tool-calling agent |
| 2 | Smart Search | 4 | Embedding similarity |
| 3 | Semantic Search | 4 | Embedding similarity |
| 4 | Product Recommendation | 4 / 7 | Similar Products = embedding (L4); Trending/New Arrival = query đơn giản, không cần AI; Frequently Bought Together = association rule mining (L7) |
| 5 | Personalized Recommendation | 7 | Collaborative/content-based filtering |
| 6 | Product Comparison | 3 | Structured output bảng so sánh + narrative |
| 7 | Review Summary | 5 | RAG / map-reduce summarization |
| 8 | Product Q&A | 5 | RAG (specs/FAQ/review/manual) |
| 9 | FAQ Assistant | 4/5 | Embedding match trước, RAG fallback |
| 10 | Cart Recommendation | 7 | Recommendation |
| 11 | Coupon Suggestion | 7 | Biến thể cá nhân hóa của Admin §3.9 |
| 12 | Size Recommendation | 7 | Rule-based (bảng size) + LLM giải thích |
| 13 | Outfit Recommendation | 7 | Rule phối đồ + LLM giải thích |
| 14 | Order Assistant | 10 | Tool-calling |
| 15 | Return Policy Assistant | 5 | RAG trên chính sách đổi trả |
| 16 | Delivery Assistant | 5/10 | RAG nếu chỉ hỏi chính sách; tool-calling nếu tra đơn thật |
| 17 | Personalized Homepage | 7 | Recommendation tổng hợp |

**Tổng Client: 17/17 ✅**

### Ghi chú về "Future Roadmap" (mục III trong bản gốc)
Các mục này được chính bạn đánh dấu là mở rộng sau, nên không cần Level riêng — chúng chỉ là biến thể nâng cao của các Level đã có:
- *AI tư vấn quà tặng, AI xây danh sách mua sắm theo ngân sách* → mở rộng Level 2/3 (thêm ràng buộc ngân sách vào structured output)
- *AI phân tích chương trình khuyến mãi, AI đề xuất giá tối ưu, AI phân tích nhóm khách hàng tiềm năng* → mở rộng Level 6 (Domain 4 logic, thêm chiều dữ liệu)
- *Natural Language Analytics (câu hỏi tự nhiên → truy vấn dữ liệu)* → mở rộng Level 10 (text-to-SQL/tool-calling)
- *AI giải thích khác biệt sản phẩm bằng ngôn ngữ đơn giản, AI thông báo sản phẩm yêu thích giảm giá, đa ngôn ngữ hội thoại* → mở rộng Level 2/5 hiện có, không cần kỹ thuật mới
