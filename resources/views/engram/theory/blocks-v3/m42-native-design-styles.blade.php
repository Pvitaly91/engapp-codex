@once('m42-finite-native-design')
<style>
/* M41's native palette; only exact source-bound theory owners receive this hook.
   Hero/layout/sidebar and practice mechanics are deliberately outside the layer. */
.theory-design .m42-native-design[data-m42-native-design]{--m42-bg:#f8fafc;--m42-line:#e2e8f0;--m42-ink:#334155;--m42-dot:#64748b;min-width:0}
.theory-design .m42-native-design[data-m42-native-design] [data-m42-color]{--m42-bg:#f8fafc;--m42-line:#e2e8f0;--m42-ink:#334155;--m42-dot:#64748b}
.theory-design .m42-native-design[data-m42-color=blue],.theory-design .m42-native-design[data-m42-native-design] [data-m42-color=blue]{--m42-bg:#eff6ff;--m42-line:#bfdbfe;--m42-ink:#1d4ed8;--m42-dot:#3b82f6}
.theory-design .m42-native-design[data-m42-color=emerald],.theory-design .m42-native-design[data-m42-native-design] [data-m42-color=emerald]{--m42-bg:#ecfdf5;--m42-line:#a7f3d0;--m42-ink:#047857;--m42-dot:#10b981}
.theory-design .m42-native-design[data-m42-color=sky],.theory-design .m42-native-design[data-m42-native-design] [data-m42-color=sky]{--m42-bg:#f0f9ff;--m42-line:#bae6fd;--m42-ink:#0369a1;--m42-dot:#0ea5e9}
.theory-design .m42-native-design[data-m42-color=amber],.theory-design .m42-native-design[data-m42-native-design] [data-m42-color=amber]{--m42-bg:#fffbeb;--m42-line:#fde68a;--m42-ink:#b45309;--m42-dot:#f59e0b}
.theory-design .m42-native-design[data-m42-color=rose],.theory-design .m42-native-design[data-m42-native-design] [data-m42-color=rose]{--m42-bg:#fff1f2;--m42-line:#fecdd3;--m42-ink:#be123c;--m42-dot:#f43f5e}
.theory-design .m42-native-design[data-m42-native-design]:not([data-m42-native-kind=practice-set]) .theory-item{border:1px solid var(--m42-line);border-radius:.75rem;background:var(--m42-bg);background-image:none}
.theory-design .m42-native-design[data-m42-native-design][data-m42-native-kind=forms-grid] .theory-item{border-color:var(--line);background:linear-gradient(135deg,var(--surface-2,var(--surface)),var(--surface-strong))}
.theory-design .m42-native-design[data-m42-native-design] .m42-point-label{color:var(--m42-ink);font-size:.75rem;line-height:1.5;margin:0}
.theory-design .m42-native-design[data-m42-native-design] .m42-point-number{background:var(--m42-dot);color:white}
.theory-design .m42-native-design[data-m42-native-design]:not([data-m42-native-kind=practice-set]) .theory-example{border:1px solid rgba(255,255,255,.8);border-radius:.5rem;background:rgba(255,255,255,.6);padding:.75rem;font-size:.75rem;line-height:1.5}
.theory-design .m42-native-design[data-m42-native-design]:not([data-m42-native-kind=practice-set]) .theory-example :is(p[lang=en],code),.theory-design .m42-native-design[data-m42-native-design] .m42-english{font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,'Liberation Mono','Courier New',monospace;font-size:.8125rem;line-height:1.6;overflow-wrap:anywhere}
.theory-design .m42-native-design[data-m42-native-design] .theory-example .theory-translation{font-family:inherit;font-size:.75rem;line-height:1.5;font-style:italic;margin-top:.125rem;color:var(--muted)}
.theory-design .m42-native-design[data-m42-native-design] .m42-rich-fragment{overflow-wrap:anywhere}
.theory-design .m42-native-design[data-m42-native-design] .m42-rich-fragment .m42-english{display:block}
.theory-design .m42-native-design[data-m42-native-design] .m42-rich-fragment .m42-form-context{display:block;margin-bottom:.35rem}
.theory-design .m42-native-design[data-m42-native-design] .m42-rich-fragment .m42-paired-translation[lang=uk]{display:block;font-family:inherit;font-size:.75rem;line-height:1.5;font-style:italic;margin-top:.125rem;color:var(--muted)}
.theory-design .m42-native-design[data-m42-native-design] .m42-rich-fragment em[data-m42-em-language=en]{font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,'Liberation Mono','Courier New',monospace;font-size:.8125rem;line-height:1.6}
.theory-design .m42-native-design[data-m42-native-design] .m42-rich-fragment em[data-m42-em-language=uk-template]{font-family:inherit}
.theory-design .m42-native-design[data-m42-native-design] .m42-rich-fragment blockquote{margin:.65rem 0;padding:.75rem;border:1px solid var(--line);border-radius:.5rem;background:var(--surface-strong)}
.theory-design .m42-native-design[data-m42-native-design] .m42-rich-fragment :is(ul,ol){padding-inline-start:1.25rem;margin:.5rem 0}
.theory-design .m42-native-design[data-m42-native-design] .m42-rich-fragment ul{list-style:disc}
.theory-design .m42-native-design[data-m42-native-design] .m42-rich-fragment ol{list-style:decimal}
.theory-design .m42-native-design[data-m42-native-design] .m42-rich-fragment p+p{margin-top:.6rem}
.theory-design .m42-native-design[data-m42-native-design]:not([data-m42-native-kind=practice-set]) .theory-rule{border:1px solid var(--line);border-radius:1rem;background:var(--surface-strong);padding:.625rem .75rem}
.theory-design .m42-native-design[data-m42-native-design] .theory-item .theory-note{border:1px solid var(--m42-line);border-radius:.5rem;background:rgba(255,255,255,.4);color:var(--muted)}
.theory-design .m42-native-design[data-m42-native-kind=comparison-table] code.theory-example{font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,'Liberation Mono','Courier New',monospace;font-size:.875rem;padding:0;border:0;background:transparent}
.theory-design .m42-native-design[data-m42-native-design] .theory-table-scroll{max-width:100%;overscroll-behavior-inline:contain}
.theory-design .m42-native-design[data-m42-native-design] .theory-table-scroll :is(th,td){overflow-wrap:anywhere}
.theory-design .m42-native-design[data-m42-native-kind=comparison-table] .theory-table-scroll :is(th,td){min-width:9rem;overflow-wrap:break-word}
.theory-design .m42-native-design[data-m42-native-kind=mistakes-grid] .theory-item .theory-example.theory-example--wrong{background:#fff1f2;border-color:#ffe4e6;color:#be123c}
.theory-design .m42-native-design[data-m42-native-kind=mistakes-grid] .theory-item .theory-example.theory-example--right{background:#ecfdf5;border-color:#d1fae5;color:#047857}
.theory-design .m42-native-design[data-m42-native-kind=mistakes-grid] .theory-example--wrong code{color:#be123c}
.theory-design .m42-native-design[data-m42-native-kind=mistakes-grid] .theory-example--right span{color:#047857}
/* Own practice: restore the existing native group colors and readable natural casing.
   Never apply explanation/example typography to controls or linked builder cards. */
.theory-design .m42-native-design[data-m42-native-kind=practice-set] .theory-exercise{border:1px solid var(--line);border-radius:.75rem;background:var(--surface-strong)}
.theory-design .m42-native-design[data-m42-native-kind=practice-set] .theory-exercise:is(.border-blue-100,.border-blue-200){border-color:#bfdbfe}
.theory-design .m42-native-design[data-m42-native-kind=practice-set] .theory-exercise:is(.border-blue-100,.border-blue-200)>.border-b{background:#eff6ff}
.theory-design .m42-native-design[data-m42-native-kind=practice-set] .theory-exercise:is(.border-amber-100,.border-amber-200){border-color:#fde68a}
.theory-design .m42-native-design[data-m42-native-kind=practice-set] .theory-exercise:is(.border-amber-100,.border-amber-200)>.border-b{background:#fffbeb}
.theory-design .m42-native-design[data-m42-native-kind=practice-set] .theory-exercise:is(.border-emerald-100,.border-emerald-200){border-color:#a7f3d0}
.theory-design .m42-native-design[data-m42-native-kind=practice-set] .theory-exercise:is(.border-emerald-100,.border-emerald-200)>.border-b{background:#ecfdf5}
.theory-design .m42-native-design[data-m42-native-kind=practice-set] .theory-exercise:is(.border-purple-100,.border-purple-200){border-color:#ddd6fe}
.theory-design .m42-native-design[data-m42-native-kind=practice-set] .theory-exercise:is(.border-purple-100,.border-purple-200)>.border-b{background:#f5f3ff}
.theory-design .m42-native-design[data-m42-native-kind=practice-set] button:is([\@click*="Answers"],[\@click*="appendInputToken"]),.theory-design .m42-native-design[data-m42-native-kind=practice-set] :is(input,textarea,option){text-transform:none;letter-spacing:normal;white-space:normal;overflow-wrap:anywhere;max-width:100%}
.theory-design .m42-native-design[data-m42-native-kind=practice-set] button{max-width:100%;white-space:normal;overflow-wrap:anywhere}
.theory-design .m42-native-design[data-m42-native-kind=practice-set] :is(input,textarea):is(.bg-emerald-50,.bg-rose-50){border-width:1px}
.dark .theory-design .m42-native-design[data-m42-native-design]{--m42-bg:#243044;--m42-line:#46546c;--m42-ink:#cbd5e1}
.dark .theory-design .m42-native-design[data-m42-native-design] [data-m42-color]{--m42-bg:#243044;--m42-line:#46546c;--m42-ink:#cbd5e1}
.dark .theory-design .m42-native-design[data-m42-color=blue],.dark .theory-design .m42-native-design[data-m42-native-design] [data-m42-color=blue]{--m42-bg:#172d4b;--m42-line:#315c8b;--m42-ink:#93c5fd}
.dark .theory-design .m42-native-design[data-m42-color=emerald],.dark .theory-design .m42-native-design[data-m42-native-design] [data-m42-color=emerald]{--m42-bg:#13372f;--m42-line:#28644d;--m42-ink:#6ee7b7}
.dark .theory-design .m42-native-design[data-m42-color=sky],.dark .theory-design .m42-native-design[data-m42-native-design] [data-m42-color=sky]{--m42-bg:#153348;--m42-line:#2d617d;--m42-ink:#7dd3fc}
.dark .theory-design .m42-native-design[data-m42-color=amber],.dark .theory-design .m42-native-design[data-m42-native-design] [data-m42-color=amber]{--m42-bg:#3a2f19;--m42-line:#79622d;--m42-ink:#fcd34d}
.dark .theory-design .m42-native-design[data-m42-color=rose],.dark .theory-design .m42-native-design[data-m42-native-design] [data-m42-color=rose]{--m42-bg:#3d2330;--m42-line:#7a4058;--m42-ink:#fda4af}
.dark .theory-design .m42-native-design[data-m42-native-design]:not([data-m42-native-kind=practice-set]) .theory-example{background:rgba(15,23,42,.45);border-color:rgba(148,163,184,.18)}
.dark .theory-design .m42-native-design[data-m42-native-design] .theory-note{background:rgba(15,23,42,.3)}
.dark .theory-design .m42-native-design[data-m42-native-kind=mistakes-grid] .theory-item .theory-example.theory-example--wrong{background:#402330;border-color:#7a4058;color:#fda4af}
.dark .theory-design .m42-native-design[data-m42-native-kind=mistakes-grid] .theory-item .theory-example.theory-example--right{background:#13372f;border-color:#28644d;color:#6ee7b7}
.dark .theory-design .m42-native-design[data-m42-native-kind=mistakes-grid] .theory-example--wrong code{color:#fda4af}
.dark .theory-design .m42-native-design[data-m42-native-kind=mistakes-grid] .theory-example--right span{color:#6ee7b7}
.dark .theory-design .m42-native-design[data-m42-native-kind=practice-set] .theory-exercise>.border-b{background:var(--surface-2,var(--surface))}
@media print{.theory-design .m42-native-design[data-m42-native-design] :is(.theory-item,.theory-example,.theory-exercise){background:white!important;color:black!important}.theory-design .m42-native-design[data-m42-native-design] .theory-table-scroll{overflow:visible}.theory-design .m42-native-design[data-m42-native-design] .theory-table-scroll table{min-width:0!important}}
</style>
@endonce
