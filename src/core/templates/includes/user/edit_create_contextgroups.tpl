<template id="groupcontextwrappertpl">
	<div class="groupcontextwraper">
		Has %%SELECT%% Access To <select name="contextgroupcontext[]" class="group-context"><option value="">-- Select Context --</option></select>
	</div>
</template>

<template id="groupcontextnames">
	<select name="contextgroup[]" class="group-select">
		<option value="">-- Select Group --</option>
		{foreach $contextnames as $title => $key}
			<option value="{$key}">{$title}</option>
		{/foreach}
	</select>
</template>

{script location="foot"}<script>
$(function(){
	var contexts = {$contexts_json},
		contextnames = {$contextnames_json},
		$groupcontainer = $('#context-groups'),
		grouptpl = $('#groupcontextwrappertpl').html();

	function addRecord(dat){
		var addanother = true,
			gid = (dat && dat.group_id) ? dat.group_id : false,
			contextpk = (dat && dat.context_pk) ? dat.context_pk : false;

		if(gid) addanother = false;

		grouptpl = grouptpl.replace(/%%SELECT%%/, $('#groupcontextnames').html());

		$group = $(grouptpl);

		$groupcontainer.append($group);

		$group.find('.group-select').change(function(){
			var val = $(this).val(),
				thiscontexts = contexts[val],
				$contextselect = $(this).closest('.groupcontextwraper').find('.group-context'),
				i;

			$contextselect.html('<option value="">-- Select Context --</option>');

			if(!thiscontexts){
				return;
			}
			else{
				for(i in thiscontexts){
					$contextselect.append('<option value="' + i + '">' + thiscontexts[i] + '</option>');
				}
			}

			// If there are no more blank entries, add another!

			$groupcontainer.find('.group-select').each(function(){
				if($(this).val() == ''){
					addanother = false;
					return false;
				}
			});

			if(addanother){
				addRecord();
			}
		});

		if(gid){
			$group.find('.group-select').val(gid).change();
			if(contextpk){
				$group.find('.group-context').val(contextpk);
			}
		}
	}

	{if $user}
		{foreach $user->getContextGroups() as $g}
			addRecord({
				group_id: '{$g.group_id}',
				context: '{$g.context}',
				context_pk: '{$g.context_pk}'
			});
		{/foreach}
	{/if}

	addRecord();
});
</script>{/script}