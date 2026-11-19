@once('m41-existing-native-design')
<style>
/* Restore declared native utilities only for the three finite M41 owners. */
.theory-design .m41-existing-design[data-m41-author-section] .theory-native-block>.theory-section-body,.theory-design .m41-existing-design[data-m41-author-section] .theory-native-block>div>.theory-section-body{padding:0}
.theory-design .m41-existing-design[data-m41-author-section] .theory-item{border:1px solid var(--line);border-radius:.75rem;background:var(--surface-strong)}
.theory-design .m41-existing-design[data-m41-author-section] .theory-item.bg-blue-50{background:#eff6ff;border-color:#bfdbfe}
.theory-design .m41-existing-design[data-m41-author-section] .theory-item.bg-emerald-50{background:#ecfdf5;border-color:#a7f3d0}
.theory-design .m41-existing-design[data-m41-author-section] .theory-item.bg-sky-50{background:#f0f9ff;border-color:#bae6fd}
.theory-design .m41-existing-design[data-m41-author-section] .theory-item.bg-amber-50{background:#fffbeb;border-color:#fde68a}
.theory-design .m41-existing-design[data-m41-author-section] .theory-item.bg-slate-50{background:#f8fafc;border-color:#e2e8f0}
.theory-design .m41-existing-design[data-m41-author-section] .theory-item.bg-rose-50{background:#fff1f2;border-color:#fecdd3}
.theory-design .m41-existing-design[data-m41-author-section] .theory-item.bg-gradient-to-br{background:linear-gradient(135deg,var(--surface-2),var(--surface-strong));border-color:var(--line)}
.theory-design .m41-existing-design[data-m41-author-section] .theory-item h3{margin:0;line-height:1.5}
.theory-design .m41-existing-design[data-m41-author-section] h3.text-blue-700{color:#1d4ed8}
.theory-design .m41-existing-design[data-m41-author-section] h3.text-emerald-700{color:#047857}
.theory-design .m41-existing-design[data-m41-author-section] h3.text-sky-700{color:#0369a1}
.theory-design .m41-existing-design[data-m41-author-section] h3.text-amber-700{color:#b45309}
.theory-design .m41-existing-design[data-m41-author-section] h3.text-rose-700{color:#be123c}
.theory-design .m41-existing-design[data-m41-author-section] h3.text-slate-700{color:#334155}
.theory-design .m41-existing-design[data-m41-author-section] .theory-item .text-xs.font-bold.uppercase{font-size:.75rem}
.theory-design .m41-existing-design[data-m41-author-section] .theory-example{border:1px solid rgba(255,255,255,.8);border-radius:.5rem;background:rgba(255,255,255,.6);padding:.75rem;border-inline-start-width:1px;font-size:.75rem;line-height:1.5}
.theory-design .m41-existing-design[data-m41-author-section] .theory-example p[lang=en],.theory-design .m41-existing-design[data-m41-author-section] .m41-error-example [data-m41-error-fragment]{font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,'Liberation Mono','Courier New',monospace;font-size:.75rem;line-height:1.5}
.theory-design .m41-existing-design[data-m41-author-section] code.theory-example{font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,'Liberation Mono','Courier New',monospace;font-size:.875rem;line-height:1.5}
.theory-design .m41-existing-design[data-m41-author-section] .theory-example .theory-translation{font-size:.75rem;line-height:1.5;margin-top:.125rem;font-style:italic}
.theory-design .m41-existing-design[data-m41-author-section] .theory-rule{border:1px solid var(--line);border-radius:1rem;background:var(--surface-strong);padding:.625rem .75rem}
.theory-design .m41-existing-design[data-m41-author-section] .theory-rule p+p{margin-top:.6rem}
.theory-design .m41-existing-design[data-m41-author-section] .m41-error-context{display:block;margin:.35rem 0}
.theory-design .m41-existing-design[data-m41-author-section] .m41-error-example{margin:.5rem 0}
.theory-design .m41-existing-design[data-m41-author-section] .m41-error-example.theory-example--wrong{background:#fff1f2;border-color:#ffe4e6;color:#be123c}
.theory-design .m41-existing-design[data-m41-author-section] .m41-error-example.theory-example--right{background:#ecfdf5;border-color:#d1fae5;color:#047857}
.theory-design .m41-existing-design[data-m41-author-section] .m41-form-note .grid{grid-template-columns:1fr}
.theory-design .m41-existing-design[data-m41-author-section] .m41-form-description p+p{margin-top:.6rem}
.theory-design .m41-existing-design[data-m41-author-section] .m41-form-columns{display:flex;flex-wrap:wrap;gap:.5rem 1rem}
.theory-design .m41-existing-design[data-m41-author-section] .theory-table-scroll{margin-bottom:.75rem}
.theory-design .m41-existing-design[data-m41-author-section] .theory-table-scroll table{min-width:36rem}
.theory-design .m41-existing-design[data-m41-author-section] .theory-note{border-radius:.5rem;background:rgba(255,255,255,.4)}
.theory-design .m41-existing-design[data-m41-author-section] .m41-native-detail .theory-example+.theory-example{margin-top:.5rem}
.dark .theory-design .m41-existing-design[data-m41-author-section] .theory-item.bg-blue-50{background:#172d4b;border-color:#315c8b}
.dark .theory-design .m41-existing-design[data-m41-author-section] .theory-item.bg-emerald-50{background:#13372f;border-color:#28644d}
.dark .theory-design .m41-existing-design[data-m41-author-section] .theory-item.bg-sky-50{background:#153348;border-color:#2d617d}
.dark .theory-design .m41-existing-design[data-m41-author-section] .theory-item.bg-amber-50{background:#3a2f19;border-color:#79622d}
.dark .theory-design .m41-existing-design[data-m41-author-section] .theory-item.bg-rose-50{background:#3d2330;border-color:#7a4058}
.dark .theory-design .m41-existing-design[data-m41-author-section] .theory-item.bg-slate-50{background:#243044;border-color:#46546c}
.dark .theory-design .m41-existing-design[data-m41-author-section] .theory-example{background:rgba(15,23,42,.45);border-color:rgba(148,163,184,.18)}
.dark .theory-design .m41-existing-design[data-m41-author-section] .m41-error-example.theory-example--wrong{background:#402330;border-color:#7a4058;color:#fda4af}
.dark .theory-design .m41-existing-design[data-m41-author-section] .m41-error-example.theory-example--right{background:#13372f;border-color:#28644d;color:#6ee7b7}
.dark .theory-design .m41-existing-design[data-m41-author-section] .theory-note{background:rgba(15,23,42,.3)}
.dark .theory-design .m41-existing-design[data-m41-author-section] h3.text-blue-700{color:#93c5fd}
.dark .theory-design .m41-existing-design[data-m41-author-section] h3.text-emerald-700{color:#6ee7b7}
.dark .theory-design .m41-existing-design[data-m41-author-section] h3.text-sky-700{color:#7dd3fc}
.dark .theory-design .m41-existing-design[data-m41-author-section] h3.text-amber-700{color:#fcd34d}
.dark .theory-design .m41-existing-design[data-m41-author-section] h3.text-rose-700{color:#fda4af}
.dark .theory-design .m41-existing-design[data-m41-author-section] h3.text-slate-700{color:#cbd5e1}
@media print{.theory-design .m41-existing-design[data-m41-author-section] .theory-item,.theory-design .m41-existing-design[data-m41-author-section] .theory-example{background:white!important;color:black!important}.theory-design .m41-existing-design[data-m41-author-section] .theory-table-scroll{overflow:visible}.theory-design .m41-existing-design[data-m41-author-section] .theory-table-scroll table{min-width:0!important}}
</style>
@endonce
