panel.plugin("adspectus/feedreader", {
  blocks: {
    feedreader: {
      template: `
        <div v-if="content.url" @dblclick="open">
          <div><span v-if="content.feedtype === 'auto'">Auto-Detect-</span><span v-if="content.feedtype === 'rss'">RSS-</span><span v-if="content.feedtype === 'atom'">Atom-</span><span v-if="content.feedtype === 'json'">JSON-</span>Feed from {{ content.url }}<span style="float: right">Cache: {{ content.usecache }}</span></div>
          <div>Show <span v-if="content.showall">all</span><span v-else>{{ content.limit }}</span> item(s) in {{ content.order }} order <span v-if="content.showartdesc">with</span><span v-else>without</span> description | Date format: {{ content.dateformat }}<span style="float: right">Validity: {{ content.cachevalidity }} Hrs.</span></div>
        </div>
        <div v-else>No URL</div>
      `
    }
  },
});
