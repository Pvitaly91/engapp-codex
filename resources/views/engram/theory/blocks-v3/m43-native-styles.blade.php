@once('m43-authored-native-design')
<style>
/* Exact M43 opt-in: only composition and wrapping. The shared theory design owns presentation. */
.theory-design .m43-native-design[data-m43-author-section] .theory-native-block>.theory-section-body,
.theory-design .m43-native-design[data-m43-author-section] .theory-native-block>div>.theory-section-body{padding:0}
.theory-design .m43-native-design[data-m43-author-section] .m43-form-point .grid{grid-template-columns:1fr;height:100%}
.theory-design .m43-native-design[data-m43-author-section] .m43-form-point .theory-item{height:100%}
.theory-design .m43-native-design[data-m43-author-section] .m43-form-note .grid{grid-template-columns:1fr}
.theory-design .m43-native-design[data-m43-author-section] .m43-form-description>p+p,
.theory-design .m43-native-design[data-m43-author-section] .m41-form-description>p+p{margin-top:.6rem}
.theory-design .m43-native-design[data-m43-author-section] .m43-form-columns{display:flex;flex-wrap:wrap;gap:.5rem 1rem}
.theory-design .m43-native-design[data-m43-author-section] .theory-example+.theory-example{margin-top:.5rem}
.theory-design .m43-native-design[data-m43-author-section] .theory-example :is(p,span){overflow-wrap:anywhere}
.theory-design .m43-native-design[data-m43-author-section] :is(.m43-example-note,.theory-example-note){margin-top:.5rem}
.theory-design .m43-native-design[data-m43-author-section] :is(.theory-table-scroll,td){min-width:0}
.theory-design .m43-native-design[data-m43-author-section] .theory-table-scroll{max-width:100%;overflow-x:auto}
.theory-design .m43-native-design[data-m43-author-section] .theory-table-scroll table{min-width:36rem}
.theory-design .m43-native-design[data-m43-author-section] .theory-table-scroll td{vertical-align:top}
.theory-design .m43-native-design[data-m43-author-section] .theory-table-scroll .theory-example{min-width:12rem}
.theory-design .m43-native-design[data-m43-practice-scope] :is(button,textarea){max-width:100%;white-space:normal;overflow-wrap:anywhere;text-transform:none}
</style>
@endonce
