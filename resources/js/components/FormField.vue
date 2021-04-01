<template>
  <default-field :field="field" :errors="errors" :show-help-text="false">
    <template slot="field">
      <div :dir="field.rtl ? 'rtl' : 'ltr'" class="p-2 nova-tree-attach-many">
        <treeselect v-model="selectedValues"
            :id="field.name"
            :multiple="field.multiple"
            :options="field.options"
            :flat="field.flatten"
            :searchable="field.searchable"
            :always-open="field.alwaysOpen"
            :disabled="field.disabled"
            :sort-value-by="field.sortValueBy"
            :placeholder="field.placeholder"
            :max-height="field.maxHeight"
            :value-consists-of="field.valueConsistsOf"
            :normalizer="normalizer"
        >
          <div slot="value-label" slot-scope="{ node }">{{ node.raw.fullname }}</div>
        </treeselect>
      </div>
    </template>
  </default-field>
</template>

<script>
import {FormField, HandlesValidationErrors} from 'laravel-nova'

import Treeselect from '@riophae/vue-treeselect'
import '@riophae/vue-treeselect/dist/vue-treeselect.css'

export default {
  components: {Treeselect},
  mixins: [FormField, HandlesValidationErrors],

  props: ['resourceName', 'resourceId', 'field'],

  data()
  {
    return {
      selectedValues: null,
    };
  },
  mounted () {
    // console.log(this.field)
  },
  methods: {
    updateValue(state, value) {
      console.log('state', state)
      console.log('value', value)
      state.value = value
    },
    normalizer( node )
    {
      return {
        id: node[this.field.idKey],
        label: node[this.field.labelKey],
        customLabel: node['fullname'],
        isDisabled: node.hasOwnProperty(this.field.activeKey)
            && node[this.field.activeKey] === this.field.isActiveFalse,
        children: node.hasOwnProperty(this.field.childrenKey)
        && node[this.field.childrenKey].length > 0
            ? node[this.field.childrenKey]
            : false
      }
    },
    setInitialValue()
    {

      let baseUrl = '/nova-vendor/address-tree-field/';

      if( this.resourceId )
      {
        const url = [
            baseUrl + this.resourceName,
            this.resourceId,
            'attached',
            this.field.attribute,
            this.field.idKey
        ];

        Nova.request( url.join('/') )
            .then( ( data ) => {

                if(!this.field.multiple)
                {
                    this.selectedValues = data.data || undefined;
                }
                else
                {
                    this.selectedValues = data.data || [];
                }
            } );
      }
      else
      {
          if(!this.field.multiple)
          {
              this.selectedValues = undefined;
          }
          else
          {
              this.selectedValues = [];
          }
      }
    },
    fill( formData )
    {
      formData.append( this.field.attribute, JSON.stringify( this.selectedValues ) )
    },
  }
}
</script>
