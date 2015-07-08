select 
    t.*,
    108 as creator_id
from todo t
where id = :key_value
