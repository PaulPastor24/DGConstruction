'use client'

import { makeStyles } from '@fluentui/react-components'

const useStyles = makeStyles({
  root: {
    backgroundColor: '#FFFFFF',
    borderRadius: '16px',
    padding: '32px',
    border: '1px solid rgba(0, 0, 0, 0.08)',
    boxShadow: '0 1px 3px rgba(0, 0, 0, 0.06)',
  },
  header: {
    marginBottom: '28px',
    paddingBottom: '20px',
    borderBottom: '1px solid rgba(0, 0, 0, 0.08)',
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
    gap: '4px',
  },
  activity: {
    display: 'flex',
    gap: '16px',
    padding: '16px',
    borderRadius: '12px',
    transition: 'all 0.2s ease',
    '&:hover': {
      backgroundColor: 'rgba(0, 0, 0, 0.02)',
    },
  },
  iconContainer: {
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    width: '40px',
    height: '40px',
    borderRadius: '50%',
    backgroundColor: 'rgba(102, 187, 106, 0.15)',
    flexShrink: 0,
    fontSize: '18px',
  },
  content: {
    display: 'flex',
    flexDirection: 'column',
    gap: '4px',
    flex: 1,
  },
  description: {
    fontSize: '14px',
    fontWeight: 500,
    color: '#333333',
  },
  timestamp: {
    fontSize: '12px',
    color: '#999999',
    fontWeight: 400,
  },
})

export function AttendanceActivityTimeline() {
  const styles = useStyles()

  const activities = [
    { icon: '✓', description: 'Michael Johnson verified fingerprint', timestamp: '08:45 AM' },
    { icon: '✓', description: 'Robert Brown verified fingerprint', timestamp: '08:42 AM' },
    { icon: '✓', description: 'David Miller verified fingerprint', timestamp: '08:40 AM' },
    { icon: '⏱', description: 'James Smith marked as late arrival (15 min)', timestamp: '08:15 AM' },
    { icon: '−', description: 'William Davis marked as absent', timestamp: '08:05 AM' },
    { icon: '📊', description: 'Attendance scan batch completed for day shift', timestamp: '08:00 AM' },
    { icon: '🔔', description: 'Supervisor added attendance remark for James Smith', timestamp: 'Yesterday 4:30 PM' },
  ]

  return (
    <div className={styles.root}>
      <div className={styles.header}>
        <div className={styles.title}>Attendance Activity Timeline</div>
        <div className={styles.subtitle}>Recent attendance events and biometric verification activities.</div>
      </div>

      <div className={styles.timeline}>
        {activities.map((activity, idx) => (
          <div key={idx} className={styles.activity}>
            <div className={styles.iconContainer}>{activity.icon}</div>
            <div className={styles.content}>
              <div className={styles.description}>{activity.description}</div>
              <div className={styles.timestamp}>{activity.timestamp}</div>
            </div>
          </div>
        ))}
      </div>
    </div>
  )
}
