select * 
from todo
where 
    (case when not
        created between :from and :to
    then 0 else 1 end) = 1
