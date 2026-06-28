'use client'

import { makeStyles } from '@fluentui/react-components'

const useStyles = makeStyles({
  root: {
    backgroundColor: '#FFFFFF',
    borderRadius: '16px',
    padding: '32px',
    boxShadow: '0 1px 3px rgba(0, 0, 0, 0.06)',
    border: '1px solid rgba(0, 0, 0, 0.08)',
  },
  header: {
    marginBottom: '28px',
  },
  title: {
    fontSize: '20px',
    fontWeight: 700,
    color: '#2F6B3C',
    marginBottom: '6px',
  },
  subtitle: {
    fontSize: '14px',
    color: '#999999',
    fontWeight: 400,
  },
  timeline: {
    display: 'flex',
    flexDirection: 'column',
    gap: '0',
  },
  activity: {
    display: 'flex',
    gap: '16px',
    paddingTop: '14px',
    paddingBottom: '14px',
    paddingLeft: '16px',
    paddingRight: '16px',
    borderBottom: '1px solid rgba(0, 0, 0, 0.06)',
    borderRadius: '10px',
    transition: 'all 0.2s ease',
    '&:last-child': {
      borderBottom: 'none',
    },
    '&:hover': {
      backgroundColor: 'rgba(0, 0, 0, 0.02)',
    },
  },
  activityIcon: {
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    width: '40px',
    height: '40px',
    borderRadius: '50%',
    backgroundColor: 'rgba(102, 187, 106, 0.15)',
    color: '#2F6B3C',
    fontSize: '20px',
    flexShrink: 0,
  },
  activityContent: {
    display: 'flex',
    flexDirection: 'column',
    gap: '4px',
    flex: 1,
  },
  activityTitle: {
    fontSize: '15px',
    fontWeight: 600,
    color: '#333333',
  },
  activityTime: {
    fontSize: '13px',
    color: '#999999',
    fontWeight: 500,
  },
  footer: {
    display: 'flex',
    justifyContent: 'center',
    marginTop: '24px',
  },
  button: {
    paddingTop: '10px',
    paddingBottom: '10px',
    paddingLeft: '24px',
    paddingRight: '24px',
    borderRadius: '10px',
    border: 'none',
    backgroundColor: 'transparent',
    color: '#2F6B3C',
    cursor: 'pointer',
    fontSize: '14px',
    fontWeight: 700,
    transition: 'all 0.2s ease',
    '&:hover': {
      color: '#1f4620',
    },
  },
})

interface Activity {
  id: string
  icon: string
  title: string
  timestamp: string
}

const activities: Activity[] = [
  {
    id: '1',
    icon: '📈',
    title: 'Roofing phase updated to 65% completion',
    timestamp: 'Today, 2:30 PM',
  },
  {
    id: '2',
    icon: '✓',
    title: 'Roof framing inspection approved by engineer',
    timestamp: 'Today, 10:15 AM',
  },
  {
    id: '3',
    icon: '👷',
    title: '8 new workers assigned to roofing team',
    timestamp: 'Yesterday, 4:45 PM',
  },
  {
    id: '4',
    icon: '📋',
    title: 'Safety checklist submitted for roofing phase',
    timestamp: 'Yesterday, 2:20 PM',
  },
  {
    id: '5',
    icon: '⏰',
    title: 'Milestone "Roof Framing Complete" approaching in 2 days',
    timestamp: 'Yesterday, 9:00 AM',
  },
  {
    id: '6',
    icon: '📝',
    title: 'Supervisor notes updated for roofing phase',
    timestamp: 'Jun 25, 3:10 PM',
  },
  {
    id: '7',
    icon: '✓',
    title: 'Framing phase completed - moved to Roofing',
    timestamp: 'Jun 24, 5:00 PM',
  },
]

export function TimelineActivityLog() {
  const styles = useStyles()

  return (
    <div className={styles.root}>
      <div className={styles.header}>
        <div className={styles.title}>Site Activity Log</div>
        <div className={styles.subtitle}>Recent changes and milestones</div>
      </div>

      <div className={styles.timeline}>
        {activities.map((activity) => (
          <div key={activity.id} className={styles.activity}>
            <div className={styles.activityIcon}>{activity.icon}</div>
            <div className={styles.activityContent}>
              <div className={styles.activityTitle}>{activity.title}</div>
              <div className={styles.activityTime}>{activity.timestamp}</div>
            </div>
          </div>
        ))}
      </div>

      <div className={styles.footer}>
        <button className={styles.button}>View All Activity</button>
      </div>
    </div>
  )
}
