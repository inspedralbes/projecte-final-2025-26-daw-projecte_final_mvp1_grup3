<template>
  <div class="overflow-x-auto">
    <table class="w-full text-left">
      <thead>
        <tr class="text-[10px] font-black text-gray-300 uppercase tracking-widest border-b border-gray-50">
          <th v-for="col in columns" :key="col.key" class="pb-6" :class="col.thClass">
            {{ col.label }}
          </th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(row, idx) in data" :key="rowKey ? row[rowKey] : idx" class="group hover:bg-gray-50 transition-all">
          <td v-for="col in columns" :key="col.key" class="py-5" :class="col.tdClass">
            <slot :name="'cell-' + col.key" :row="row" :value="row[col.key]">
              {{ row[col.key] }}
            </slot>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
export default {
  name: 'AdminDataTable',
  props: {
    columns: { type: Array, required: true },
    data: { type: Array, default: function () { return []; } },
    rowKey: { type: String, default: 'id' }
  }
};
</script>
